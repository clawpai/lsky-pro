<?php

declare(strict_types=1);

return [
    'model_label' => '图片处理驱动器',
    'plural_model_label' => '图片处理',
    'columns' => [
        'name' => '驱动名称',
        'intro' => '简介',
        'created_at' => '创建时间',
    ],
    'actions' => [],
    'tabs' => [
        'basic' => '基本',
        'crop' => '裁剪',
        'resize' => '尺寸',
        'filter' => '滤镜',
        'rotate' => '旋转',
        'text' => '文本',
        'watermark' => '水印',
        'output' => '输出',
    ],
    'form_fields' => [
        'name' => [
            'label' => '驱动名称',
            'placeholder' => '输入驱动名称',
        ],
        'intro' => [
            'label' => '简介',
            'placeholder' => '输入简介',
        ],
        'options_is_sync' => [
            'label' => '同步处理',
            'helper_text' => '默认为异步(上传成功后通过异步队列进行处理，处理完毕后覆盖原图)，如果设置为同步处理，上传图片时直接对原图按照规则进行处理。注意，同步处理会使上传时间增加。',
        ],
        'options_output' => [
            'label' => '输出设置',
            'fields' => [
                'encoder' => [
                    'label' => '输出格式',
                    'placeholder' => '请选择输出格式',
                    'helper_text' => '修改图片格式的同时会将文件拓展名一并修改，仅「同步处理」时可设置。'
                ],
                'quality' => [
                    'label' => '图片质量',
                    'placeholder' => '输出图片质量，推荐 75（0 - 100）',
                    'helper_text' => '仅支持webp、jpg、jpeg、avif、tiff、jp2、heic。'
                ],
            ]
        ],
        'operations' => [
            'add_action_label' => '添加处理规则'
        ]
    ],
    'operations' => [
        // 裁剪
        'cover' => [
            'label' => '裁剪并缩放(结合裁剪和缩放，将图像裁剪并调整为目标尺寸)',
            'fields' => [
                'width' => [
                    'label' => '宽',
                    'placeholder' => '请输入宽度',
                ],
                'height' => [
                    'label' => '高',
                    'placeholder' => '请输入高度',
                ],
                'position' => [
                    'label' => '位置',
                    'placeholder' => '选择裁剪位置',
                ],
            ]
        ],
        'cover_down' => [
            'label' => '裁剪并缩放(不超出原尺寸)',
            'fields' => [
                'width' => [
                    'label' => '宽',
                    'placeholder' => '请输入宽度',
                ],
                'height' => [
                    'label' => '高',
                    'placeholder' => '请输入高度',
                ],
                'position' => [
                    'label' => '位置',
                    'placeholder' => '选择裁剪位置',
                ],
            ]
        ],
        'crop' => [
            'label' => '裁剪图片(裁剪出指定大小的矩形区域)',
            'fields' => [
                'width' => [
                    'label' => '宽',
                    'placeholder' => '请输入宽度',
                ],
                'height' => [
                    'label' => '高',
                    'placeholder' => '请输入高度',
                ],
                'offset_x' => [
                    'label' => 'X 轴偏移像素',
                    'placeholder' => '请输入 X 轴偏移像素',
                ],
                'offset_y' => [
                    'label' => 'Y 轴偏移像素',
                    'placeholder' => '请输入 Y 轴偏移像素',
                ],
                'background' => [
                    'label' => '新区域填充颜色',
                    'placeholder' => '请选择新创建区域填充颜色'
                ],
                'position' => [
                    'label' => '位置',
                    'placeholder' => '选择裁剪位置',
                ],
            ]
        ],
        'trim' => [
            'label' => '修剪图像(去除颜色相似的边框区域)',
            'fields' => [
                'tolerance' => [
                    'label' => '容差值',
                    'placeholder' => '请输入容差值',
                ],
            ]
        ],

        // 尺寸
        'resize' => [
            'label' => '调整为指定尺寸',
            'fields' => [
                'width' => [
                    'label' => '宽',
                    'placeholder' => '请输入宽度',
                ],
                'height' => [
                    'label' => '高',
                    'placeholder' => '请输入高度',
                ],
            ]
        ],
        'resize_down' => [
            'label' => '调整为指定尺寸(不超出原尺寸)',
            'fields' => [
                'width' => [
                    'label' => '宽',
                    'placeholder' => '请输入宽度，可为空',
                ],
                'height' => [
                    'label' => '高',
                    'placeholder' => '请输入高度，可为空',
                ],
            ]
        ],
        'scale' => [
            'label' => '按比例缩放',
            'fields' => [
                'width' => [
                    'label' => '宽',
                    'placeholder' => '请输入宽度，可为空',
                ],
                'height' => [
                    'label' => '高',
                    'placeholder' => '请输入高度，可为空',
                ],
            ]
        ],
        'scale_down' => [
            'label' => '按比例缩放(不超出原尺寸)',
            'fields' => [
                'width' => [
                    'label' => '宽',
                    'placeholder' => '请输入宽度，可为空',
                ],
                'height' => [
                    'label' => '高',
                    'placeholder' => '请输入高度，可为空',
                ],
            ]
        ],
        'pad' => [
            'label' => '填充缩放(将图像缩放到适合的目标尺寸，未填满的区域以指定颜色填充)',
            'fields' => [
                'width' => [
                    'label' => '宽',
                    'placeholder' => '请输入宽度',
                ],
                'height' => [
                    'label' => '高',
                    'placeholder' => '请输入高度',
                ],
                'background' => [
                    'label' => '新区域填充颜色',
                    'placeholder' => '请选择新创建区域填充颜色'
                ],
                'position' => [
                    'label' => '位置',
                    'placeholder' => '选择裁剪位置',
                ],
            ]
        ],
        'contain' => [
            'label' => '包含缩放(与填充缩放类似，将原始图像放大到目标尺寸)',
            'fields' => [
                'width' => [
                    'label' => '宽',
                    'placeholder' => '请输入宽度',
                ],
                'height' => [
                    'label' => '高',
                    'placeholder' => '请输入高度',
                ],
                'background' => [
                    'label' => '新区域填充颜色',
                    'placeholder' => '请选择新创建区域填充颜色'
                ],
                'position' => [
                    'label' => '位置',
                    'placeholder' => '选择裁剪位置',
                ],
            ]
        ],
        'resize_canvas' => [
            'label' => '调整画布(调整图像边界大小，不对图像本身重采样)',
            'fields' => [
                'width' => [
                    'label' => '宽',
                    'placeholder' => '可选，请输入宽度',
                ],
                'height' => [
                    'label' => '高',
                    'placeholder' => '可选，请输入高度',
                ],
                'background' => [
                    'label' => '新区域填充颜色',
                    'placeholder' => '请选择新创建区域填充颜色'
                ],
                'position' => [
                    'label' => '位置',
                    'placeholder' => '选择裁剪位置',
                ],
            ]
        ],
        'resize_canvas_relative' => [
            'label' => '相对调整画布(类似于调整画布，但接受相对值)',
            'fields' => [
                'width' => [
                    'label' => '宽',
                    'placeholder' => '可选，请输入宽度',
                ],
                'height' => [
                    'label' => '高',
                    'placeholder' => '可选，请输入高度',
                ],
                'background' => [
                    'label' => '新区域填充颜色',
                    'placeholder' => '请选择新创建区域填充颜色'
                ],
                'position' => [
                    'label' => '位置',
                    'placeholder' => '选择裁剪位置',
                ],
            ]
        ],

        // 滤镜
        'brightness' => [
            'label' => '改变亮度',
            'fields' => [
                'level' => [
                    'label' => '亮度值',
                    'placeholder' => '请输入亮度值，-100（最小亮度），0（无变化），100（最大亮度）',
                ],
            ]
        ],
        'contrast' => [
            'label' => '改变对比度',
            'fields' => [
                'level' => [
                    'label' => '对比度值',
                    'placeholder' => '请输入对比度值，-100（最小对比度），0（无变化），100（最对比度）',
                ],
            ]
        ],
        'gamma' => [
            'label' => '伽马校正',
            'fields' => [
                'gamma' => [
                    'label' => '伽马补偿值',
                    'placeholder' => '请输入伽马补偿值',
                ],
            ]
        ],
        'colorize' => [
            'label' => '颜色校正',
            'fields' => [
                'red' => [
                    'label' => '红色',
                    'placeholder' => '红色强度级别，100(最大颜色强度)，0（无变化），-100（删除所有红色）',
                ],
                'green' => [
                    'label' => '绿色',
                    'placeholder' => '绿色强度级别，100(最大颜色强度)，0（无变化），-100（删除所有绿色）',
                ],
                'blue' => [
                    'label' => '蓝色',
                    'placeholder' => '蓝色强度级别，100(最大颜色强度)，0（无变化），-100（删除所有蓝色）',
                ],
            ]
        ],
        'greyscale' => [
            'label' => '转换为灰度版本（无选项）',
            'fields' => []
        ],
        'flop' => [
            'label' => '水平镜像（无选项）',
            'fields' => []
        ],
        'flip' => [
            'label' => '垂直镜像（无选项）',
            'fields' => []
        ],
        'blur' => [
            'label' => '模糊效果',
            'fields' => [
                'amount' => [
                    'label' => '效果强度值',
                    'placeholder' => '请输入效果强度值（0 - 100）',
                ],
            ]
        ],
        'sharpen' => [
            'label' => '锐化效果',
            'fields' => [
                'amount' => [
                    'label' => '效果强度值',
                    'placeholder' => '请输入效果强度值（0 - 100）',
                ],
            ]
        ],
        'invert' => [
            'label' => '反转颜色（无选项）',
            'fields' => []
        ],
        'pixelate' => [
            'label' => '像素化效果',
            'fields' => [
                'size' => [
                    'label' => '像素的大小',
                    'placeholder' => '请输入像素的大小',
                ],
            ]
        ],

        // 旋转
        'rotate' => [
            'label' => '图像旋转',
            'fields' => [
                'angle' => [
                    'label' => '旋转角度',
                    'placeholder' => '请输入旋转角度，（以度为单位）以逆时针方向旋转图像',
                ],
                'background' => [
                    'label' => '新区域填充颜色',
                    'placeholder' => '请选择新创建区域填充颜色'
                ],
            ]
        ],

        // 文本
        'text' => [
            'label' => '文本设置',
            'fields' => [
                'text' => [
                    'label' => '文本内容',
                    'placeholder' => '请输入文本内容',
                ],
                'position' => [
                    'label' => '文本位置',
                    'placeholder' => '请选择文本位置',
                ],
                'offset_x' => [
                    'label' => 'X 轴偏移像素',
                    'placeholder' => '请输入 X 轴偏移像素'
                ],
                'offset_y' => [
                    'label' => 'Y 轴偏移像素',
                    'placeholder' => '请输入 Y 轴偏移像素'
                ],
                'ratio' => [
                    'label' => '文本大小比例值',
                    'placeholder' => '请输入比例值（0 - 100）',
                    'helper_text' => '请输入文本大小比例值，用于根据上传的图片大小动态计算文本大小。计算公式：目标图片大小*文本宽或高/100'
                ],
                'is_tiled' => [
                    'label' => '是否平铺文本',
                    'helper_text' => '是否将文本铺满整张图片',
                ],
                'font' => [
                    'label' => '字体配置',
                    'fields' => [
                        'filename' => [
                            'fields' => [
                                'value' => [
                                    'label' => '字体文件',
                                    'placeholder' => '请上传字体文件',
                                ]
                            ]
                        ],
                        'size' => [
                            'fields' => [
                                'value' => [
                                    'label' => '字体大小',
                                    'placeholder' => '请输入字体大小，值越大文本质量越高'
                                ]
                            ]
                        ],
                        'color' => [
                            'fields' => [
                                'value' => [
                                    'label' => '颜色',
                                    'placeholder' => '请选择字体颜色'
                                ]
                            ]
                        ],
                        'stroke' => [
                            'label' => '文字描边（轮廓）',
                            'fields' => [
                                'color' => [
                                    'label' => '颜色',
                                    'placeholder' => '请选择字体颜色'
                                ],
                                'width' => [
                                    'label' => '宽度',
                                    'placeholder' => '请输入宽度（0 - 10）'
                                ]
                            ]
                        ],
                        'align' => [
                            'fields' => [
                                'value' => [
                                    'label' => '水平对齐方式',
                                    'placeholder' => '请选择水平对齐方式'
                                ]
                            ]
                        ],
                        'valign' => [
                            'fields' => [
                                'value' => [
                                    'label' => '垂直对齐方式',
                                    'placeholder' => '请选择垂直对齐方式'
                                ]
                            ]
                        ],
                        'angle' => [
                            'fields' => [
                                'value' => [
                                    'label' => '旋转角度',
                                    'placeholder' => '请输入旋转角度，（以度为单位）以逆时针方向旋转图像'
                                ]
                            ]
                        ],
                        'line_height' => [
                            'fields' => [
                                'value' => [
                                    'label' => '文本行高',
                                    'placeholder' => '请输入文本行高'
                                ]
                            ]
                        ],
                        'wrap' => [
                            'label' => '文本换行',
                            'fields' => [
                                'width' => [
                                    'label' => '最大宽度',
                                    'placeholder' => '可为空，输入文本块的最大宽度（以像素为单位）'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],

        // 水印
        'watermark' => [
            'label' => '水印设置',
            'fields' => [
                'element' => [
                    'label' => '水印图片',
                    'placeholder' => '请选择水印图片'
                ],
                'position' => [
                    'label' => '水印位置',
                    'placeholder' => '请选择水印位置',
                ],
                'offset_x' => [
                    'label' => 'X 轴偏移像素',
                    'placeholder' => '请输入 X 轴偏移像素',
                ],
                'offset_y' => [
                    'label' => 'Y 轴偏移像素',
                    'placeholder' => '请输入 Y 轴偏移像素',
                ],
                'opacity' => [
                    'label' => '不透明度',
                    'placeholder' => '图像的不透明度，范围从 0（完全透明）到 100（不透明）'
                ],
                'ratio' => [
                    'label' => '水印大小比例值',
                    'placeholder' => '请输入比例值（0 - 100）',
                    'helper_text' => '请输入水印大小比例值，用于根据上传的图片大小动态计算水印图片大小。计算公式：目标图片大小*水印图片宽或高/100'
                ],
                'is_tiled' => [
                    'label' => '是否平铺水印',
                    'helper_text' => '是否将水印铺满整张图片',
                ],
            ],
        ],
    ]
];
