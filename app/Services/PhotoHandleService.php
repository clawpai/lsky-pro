<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Photo;
use ArrayObject;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Imagick;
use ImagickDraw;
use Intervention\Image\Drivers\AbstractEncoder;
use Intervention\Image\Encoders\{AutoEncoder,
    AvifEncoder,
    BmpEncoder,
    GifEncoder,
    HeicEncoder,
    Jpeg2000Encoder,
    JpegEncoder,
    MediaTypeEncoder,
    PngEncoder,
    TiffEncoder,
    WebpEncoder};
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\MediaType;
use Intervention\Image\Typography\Font;
use League\Flysystem\FilesystemException;

class PhotoHandleService
{
    /**
     * 获取可处理的位置
     * @return string[]
     */
    public function getPositions(): array
    {
        return [
            'center' => '中间',
            'top' => '上边',
            'left' => '左边',
            'top-left' => '左上',
            'bottom-left' => '左下',
            'right' => '右边',
            'top-right' => '右上',
            'bottom-right' => '右下',
            'bottom' => '下边',
        ];
    }

    /**
     * 获取可用的编码器
     *
     * @return string[]
     */
    public function getEncoders(): array
    {
        return [
            AutoEncoder::class => '自动',
            JpegEncoder::class => 'jpeg',
            WebpEncoder::class => 'webp',
            PngEncoder::class => 'png',
            GifEncoder::class => 'gif',
            AvifEncoder::class => 'avif',
            BmpEncoder::class => 'bmp',
            TiffEncoder::class => 'tiff',
            Jpeg2000Encoder::class => 'jp2',
            HeicEncoder::class => 'heic',
        ];
    }

    /**
     * 给目标图片插入水印图片
     *
     * @param ImageInterface $image 目标图片
     * @param ImageInterface $watermark 水印图片
     * @param int $ratio 图片比例值 水印图片大小=目标图片*比例值/100
     * @param bool $isTiled 是否将水印平铺整个目标图片上
     * @param array $appends 传入 place 方法中的额外选项
     * @return ImageInterface
     */
    public function placeWatermark(
        ImageInterface $image,
        ImageInterface $watermark,
        int $ratio = 10,
        bool $isTiled = false,
        array $appends = [],
    ): ImageInterface
    {
        $width = $image->width();
        $height = $image->height();

        // 计算水印大小
        $watermark->scaleDown(width: (int)($width * $ratio / 100), height: (int)($height * $ratio / 100));

        $offsetX = (int) data_get($appends, 'offset_x', 0);
        $offsetY = (int) data_get($appends, 'offset_y', 0);

        // 是否平铺水印
        if ($isTiled) {
            $watermarkWidth = $watermark->width();
            $watermarkHeight = $watermark->height();

            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $image->place($watermark, '', $x, $y);
                    $y += $watermarkHeight + $offsetY;
                }
                $x += $watermarkWidth + $offsetX;
            }

            return $image;
        }

        $arguments = array_merge(Arr::only($appends, ['position', 'offset_x', 'offset_y', 'opacity']), [
            'element' => $watermark,
        ]);

        return $image->place(...$arguments);
    }

    /**
     * 根据配置格式化图片
     * @param Photo $photo 图片
     * @param ArrayObject $options 处理驱动配置
     * @return bool
     * @throws FilesystemException
     * @throws \ImagickDrawException
     * @throws \ImagickException
     */
    public function format(Photo $photo, ArrayObject $options): bool
    {
        $filesystem = $photo->filesystem();

        $image = Image::read($filesystem->readStream($photo->pathname));

        // 处理图片
        foreach (data_get($options, 'operations', []) as $operate => $methods) {
            foreach ($methods as $value) {
                $method = data_get($value, 'type');
                $arguments = data_get($value, 'data', []);

                if (! method_exists($image, $method)) {
                    continue;
                }

                switch ($operate) {
                    case 'crop': // 缩放
                    case 'filter': // 滤镜
                    case 'resize': // 缩放
                    case 'rotate': // 尺寸
                        $image->{$method}(...$arguments);
                        break;
                    case 'text': // 文本
                    case 'watermark': // 水印
                        if ($operate === 'text') {
                            $text = data_get($arguments, 'text', '');
                            $filename = Storage::disk('local')->path(data_get($arguments, 'font.filename.value', ''));
                            $fontSize = (float)data_get($arguments, 'font.size.value', 12);

                            // 先获取文本的宽高
                            // @see https://github.com/Intervention/image/pull/779
                            $draw = new ImagickDraw();
                            $draw->setTextAntialias(true);
                            $draw->setFont($filename);
                            $draw->setFontSize($fontSize);
                            $dimensions = (new Imagick())->queryFontMetrics($draw, $text);

                            $watermarkWidth = intval(abs($dimensions['textWidth']));
                            $watermarkHeight = intval(abs($dimensions['textHeight']));

                            // 将文本插入到水印图片中
                            $font = new Font($filename);
                            $font->setSize($fontSize);
                            $font->setStrokeColor(data_get($arguments, 'font.stroke.color'));
                            $font->setStrokeWidth((int)data_get($arguments, 'font.stroke.width', 1));
                            $font->setValignment(data_get($arguments, 'font.valign.value', 'bottom'));
                            $font->setAlignment(data_get($arguments, 'font.align.value', 'left'));
                            $font->setLineHeight((float)data_get($arguments, 'font.lineHeight.value', 1.25));
                            // $font->setAngle((float)data_get($arguments, 'font.angle.value', 0));
                            $font->setWrapWidth(data_get($arguments, 'font.wrap.width'));
                            $font->setColor(data_get($arguments, 'font.color.value', '#000000'));

                            $watermark = Image::create($watermarkWidth, $watermarkHeight);
                            $watermark->text($text, 0, 0, $font)->trim(); // 去除多余空余部分

                            // 旋转图片而不是旋转文字，否则文字可能会出现溢出的情况
                            $watermark->rotate(
                                angle: (float)data_get($arguments, 'font.angle.value', 0),
                                background: 'transparent'
                            );
                        } else {
                            $watermark = Image::read(Storage::disk('local')->path($arguments['element']));
                        }

                        $this->placeWatermark(
                            image: $image,
                            watermark: $watermark,
                            ratio: (int)data_get($arguments, 'ratio', 10),
                            isTiled: (bool)data_get($arguments, 'is_tiled', false),
                            appends: [
                                'offset_x' => (int)data_get($arguments, 'offset_x', 0),
                                'offset_y' => (int)data_get($arguments, 'offset_y', 0),
                                'position' => data_get($arguments, 'position', 'top-left'),
                                'opacity' => (int)data_get($arguments, 'opacity', 100),
                            ],
                        );

                        unset($watermark, $draw, $dimensions, $font);

                        break;
                }
            }
        }

        $result = $image->encode($this->getOutputEncoder($options));

        // 新的拓展名
        $extension = MediaType::from($result->mediaType())->fileExtension()->value;

        // 替换新的拓展名
        $pathname = $photo->pathname;
        $photo->pathname = Str::replaceLast($photo->extension, $extension, $pathname);

        // 储存新的图片
        $filesystem->writeStream($photo->pathname, $result->toFilePointer());

        // 修改了拓展名，删除旧文件
        if ($photo->isDirty('pathname')) {
            $filesystem->delete($pathname);
        }

        // 重新设置基本信息
        $photo->fill([
            'mimetype' => $result->mimetype(),
            'extension' => $extension,
            'md5' => md5($result->toString()),
            'sha1' => sha1($result->toString()),
            'width' => $image->width(),
            'height' => $image->height(),
            'size' => $result->size() / 1024,
        ]);

        // 释放内存
        unset($result, $image, $filesystem);

        return $photo->save();
    }

    /**
     * 获取指定输出的编码器
     *
     * @param ArrayObject $options
     * @return AbstractEncoder
     */
    public function getOutputEncoder(ArrayObject $options): AbstractEncoder
    {
        /** @var MediaTypeEncoder $encoder */
        $encoder = data_get($options, 'output.encoder', AutoEncoder::class);

        $arguments = [];

        // 仅支持设置质量的编码器
        if (in_array($encoder, [
            AutoEncoder::class,
            JpegEncoder::class,
            WebpEncoder::class,
            AvifEncoder::class,
            TiffEncoder::class,
            Jpeg2000Encoder::class,
            HeicEncoder::class,
        ])) {
            $arguments['quality'] = (int)data_get($options, 'output.quality', AbstractEncoder::DEFAULT_QUALITY);
        }

        return new $encoder(...$arguments);
    }
}