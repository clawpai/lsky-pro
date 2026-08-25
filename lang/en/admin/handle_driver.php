<?php

declare(strict_types=1);

return [
    'model_label' => 'Image Processing Driver',
    'plural_model_label' => 'Image Processing',
    'columns' => [
        'name' => 'Driver Name',
        'intro' => 'Introduction',
        'created_at' => 'Creation Time',
    ],
    'actions' => [],
    'tabs' => [
        'basic' => 'Basic',
        'crop' => 'Crop',
        'resize' => 'Resize',
        'filter' => 'Filter',
        'rotate' => 'Rotate',
        'text' => 'Text',
        'watermark' => 'Watermark',
        'output' => 'Output',
    ],
    'form_fields' => [
        'name' => [
            'label' => 'Driver Name',
            'placeholder' => 'Enter Driver Name',
        ],
        'intro' => [
            'label' => 'Introduction',
            'placeholder' => 'Enter Introduction',
        ],
        'options_is_sync' => [
            'label' => 'Synchronous Processing',
            'helper_text' => 'Default is asynchronous (processed through an asynchronous queue after successful upload, and the original image is overwritten after processing). If set to synchronous processing, the original image will be processed according to the rules directly upon upload. Note that synchronous processing will increase upload time.',
        ],
        'options_output' => [
            'label' => 'Output Settings',
            'fields' => [
                'encoder' => [
                    'label' => 'Output Format',
                    'placeholder' => 'Please select output format',
                    'helper_text' => 'Changing the image format will also change the file extension. This can only be set during "Synchronous Processing."'
                ],
                'quality' => [
                    'label' => 'Image Quality',
                    'placeholder' => 'Output image quality, recommended 75 (0 - 100)',
                    'helper_text' => 'Only supports webp, jpg, jpeg, avif, tiff, jp2, heic.'
                ],
            ]
        ],
        'operations' => [
            'add_action_label' => 'Add Processing Rule'
        ]
    ],
    'operations' => [
        // Crop
        'cover' => [
            'label' => 'Crop and Resize (Combine cropping and resizing to crop the image and adjust it to the target size)',
            'fields' => [
                'width' => [
                    'label' => 'Width',
                    'placeholder' => 'Enter width',
                ],
                'height' => [
                    'label' => 'Height',
                    'placeholder' => 'Enter height',
                ],
                'position' => [
                    'label' => 'Position',
                    'placeholder' => 'Select crop position',
                ],
            ]
        ],
        'cover_down' => [
            'label' => 'Crop and Resize (Not exceeding original size)',
            'fields' => [
                'width' => [
                    'label' => 'Width',
                    'placeholder' => 'Enter width',
                ],
                'height' => [
                    'label' => 'Height',
                    'placeholder' => 'Enter height',
                ],
                'position' => [
                    'label' => 'Position',
                    'placeholder' => 'Select crop position',
                ],
            ]
        ],
        'crop' => [
            'label' => 'Crop Image (Crop a rectangular area of specified size)',
            'fields' => [
                'width' => [
                    'label' => 'Width',
                    'placeholder' => 'Enter width',
                ],
                'height' => [
                    'label' => 'Height',
                    'placeholder' => 'Enter height',
                ],
                'offset_x' => [
                    'label' => 'X-Axis Offset Pixels',
                    'placeholder' => 'Enter X-axis offset pixels',
                ],
                'offset_y' => [
                    'label' => 'Y-Axis Offset Pixels',
                    'placeholder' => 'Enter Y-axis offset pixels',
                ],
                'background' => [
                    'label' => 'New Area Fill Color',
                    'placeholder' => 'Please select fill color for the newly created area'
                ],
                'position' => [
                    'label' => 'Position',
                    'placeholder' => 'Select crop position',
                ],
            ]
        ],
        'trim' => [
            'label' => 'Trim Image (Remove border areas with similar colors)',
            'fields' => [
                'tolerance' => [
                    'label' => 'Tolerance Value',
                    'placeholder' => 'Enter tolerance value',
                ],
            ]
        ],

        // Resize
        'resize' => [
            'label' => 'Adjust to Specified Size',
            'fields' => [
                'width' => [
                    'label' => 'Width',
                    'placeholder' => 'Enter width',
                ],
                'height' => [
                    'label' => 'Height',
                    'placeholder' => 'Enter height',
                ],
            ]
        ],
        'resize_down' => [
            'label' => 'Adjust to Specified Size (Not exceeding original size)',
            'fields' => [
                'width' => [
                    'label' => 'Width',
                    'placeholder' => 'Enter width, can be empty',
                ],
                'height' => [
                    'label' => 'Height',
                    'placeholder' => 'Enter height, can be empty',
                ],
            ]
        ],
        'scale' => [
            'label' => 'Scale Proportionally',
            'fields' => [
                'width' => [
                    'label' => 'Width',
                    'placeholder' => 'Enter width, can be empty',
                ],
                'height' => [
                    'label' => 'Height',
                    'placeholder' => 'Enter height, can be empty',
                ],
            ]
        ],
        'scale_down' => [
            'label' => 'Scale Proportionally (Not exceeding original size)',
            'fields' => [
                'width' => [
                    'label' => 'Width',
                    'placeholder' => 'Enter width, can be empty',
                ],
                'height' => [
                    'label' => 'Height',
                    'placeholder' => 'Enter height, can be empty',
                ],
            ]
        ],
        'pad' => [
            'label' => 'Pad and Resize (Scale the image to fit the target size, filling any unfilled areas with the specified color)',
            'fields' => [
                'width' => [
                    'label' => 'Width',
                    'placeholder' => 'Enter width',
                ],
                'height' => [
                    'label' => 'Height',
                    'placeholder' => 'Enter height',
                ],
                'background' => [
                    'label' => 'New Area Fill Color',
                    'placeholder' => 'Please select fill color for the newly created area'
                ],
                'position' => [
                    'label' => 'Position',
                    'placeholder' => 'Select crop position',
                ],
            ]
        ],
        'contain' => [
            'label' => 'Contain Resize (Similar to pad and resize, enlarges the original image to the target size)',
            'fields' => [
                'width' => [
                    'label' => 'Width',
                    'placeholder' => 'Enter width',
                ],
                'height' => [
                    'label' => 'Height',
                    'placeholder' => 'Enter height',
                ],
                'background' => [
                    'label' => 'New Area Fill Color',
                    'placeholder' => 'Please select fill color for the newly created area'
                ],
                'position' => [
                    'label' => 'Position',
                    'placeholder' => 'Select crop position',
                ],
            ]
        ],
        'resize_canvas' => [
            'label' => 'Resize Canvas (Adjust the canvas size without resampling the image itself)',
            'fields' => [
                'width' => [
                    'label' => 'Width',
                    'placeholder' => 'Optional, enter width',
                ],
                'height' => [
                    'label' => 'Height',
                    'placeholder' => 'Optional, enter height',
                ],
                'background' => [
                    'label' => 'New Area Fill Color',
                    'placeholder' => 'Please select fill color for the newly created area'
                ],
                'position' => [
                    'label' => 'Position',
                    'placeholder' => 'Select crop position',
                ],
            ]
        ],
        'resize_canvas_relative' => [
            'label' => 'Resize Canvas Relatively (Similar to resize canvas, but accepts relative values)',
            'fields' => [
                'width' => [
                    'label' => 'Width',
                    'placeholder' => 'Optional, enter width',
                ],
                'height' => [
                    'label' => 'Height',
                    'placeholder' => 'Optional, enter height',
                ],
                'background' => [
                    'label' => 'New Area Fill Color',
                    'placeholder' => 'Please select fill color for the newly created area'
                ],
                'position' => [
                    'label' => 'Position',
                    'placeholder' => 'Select crop position',
                ],
            ]
        ],

        // Filter
        'brightness' => [
            'label' => 'Change Brightness',
            'fields' => [
                'level' => [
                    'label' => 'Brightness Value',
                    'placeholder' => 'Enter brightness value, -100 (minimum brightness), 0 (no change), 100 (maximum brightness)',
                ],
            ]
        ],
        'contrast' => [
            'label' => 'Change Contrast',
            'fields' => [
                'level' => [
                    'label' => 'Contrast Value',
                    'placeholder' => 'Enter contrast value, -100 (minimum contrast), 0 (no change), 100 (maximum contrast)',
                ],
            ]
        ],
        'gamma' => [
            'label' => 'Gamma Correction',
            'fields' => [
                'gamma' => [
                    'label' => 'Gamma Compensation Value',
                    'placeholder' => 'Enter gamma compensation value',
                ],
            ]
        ],
        'colorize' => [
            'label' => 'Color Correction',
            'fields' => [
                'red' => [
                    'label' => 'Red',
                    'placeholder' => 'Red intensity level, 100 (maximum color intensity), 0 (no change), -100 (remove all red)',
                ],
                'green' => [
                    'label' => 'Green',
                    'placeholder' => 'Green intensity level, 100 (maximum color intensity), 0 (no change), -100 (remove all green)',
                ],
                'blue' => [
                    'label' => 'Blue',
                    'placeholder' => 'Blue intensity level, 100 (maximum color intensity), 0 (no change), -100 (remove all blue)',
                ],
            ]
        ],
        'greyscale' => [
            'label' => 'Convert to Grayscale Version (No options)',
            'fields' => []
        ],
        'flop' => [
            'label' => 'Horizontal Flip (No options)',
            'fields' => []
        ],
        'flip' => [
            'label' => 'Vertical Flip (No options)',
            'fields' => []
        ],
        'blur' => [
            'label' => 'Blur Effect',
            'fields' => [
                'amount' => [
                    'label' => 'Effect Intensity Value',
                    'placeholder' => 'Enter effect intensity value (0 - 100)',
                ],
            ]
        ],
        'sharpen' => [
            'label' => 'Sharpen Effect',
            'fields' => [
                'amount' => [
                    'label' => 'Effect Intensity Value',
                    'placeholder' => 'Enter effect intensity value (0 - 100)',
                ],
            ]
        ],
        'invert' => [
            'label' => 'Invert Colors (No options)',
            'fields' => []
        ],
        'pixelate' => [
            'label' => 'Pixelate Effect',
            'fields' => [
                'size' => [
                    'label' => 'Pixel Size',
                    'placeholder' => 'Enter pixel size',
                ],
            ]
        ],

        // Rotate
        'rotate' => [
            'label' => 'Image Rotation',
            'fields' => [
                'angle' => [
                    'label' => 'Rotation Angle',
                    'placeholder' => 'Enter rotation angle (in degrees) to rotate the image counterclockwise',
                ],
                'background' => [
                    'label' => 'New Area Fill Color',
                    'placeholder' => 'Please select fill color for the newly created area'
                ],
            ]
        ],

        // Text
        'text' => [
            'label' => 'Text Settings',
            'fields' => [
                'text' => [
                    'label' => 'Text Content',
                    'placeholder' => 'Enter text content',
                ],
                'position' => [
                    'label' => 'Text Position',
                    'placeholder' => 'Please select text position',
                ],
                'offset_x' => [
                    'label' => 'X-Axis Offset Pixels',
                    'placeholder' => 'Enter X-axis offset pixels'
                ],
                'offset_y' => [
                    'label' => 'Y-Axis Offset Pixels',
                    'placeholder' => 'Enter Y-axis offset pixels'
                ],
                'ratio' => [
                    'label' => 'Text Size Ratio',
                    'placeholder' => 'Enter ratio value (0 - 100)',
                    'helper_text' => 'Enter the text size ratio value to dynamically calculate the text size based on the uploaded image size. Calculation formula: Target image size * text width or height / 100'
                ],
                'is_tiled' => [
                    'label' => 'Tile Text',
                    'helper_text' => 'Whether to tile the text across the entire image',
                ],
                'font' => [
                    'label' => 'Font Configuration',
                    'fields' => [
                        'filename' => [
                            'fields' => [
                                'value' => [
                                    'label' => 'Font File',
                                    'placeholder' => 'Please upload font file',
                                ]
                            ]
                        ],
                        'size' => [
                            'fields' => [
                                'value' => [
                                    'label' => 'Font Size',
                                    'placeholder' => 'Enter font size, the larger the value, the higher the text quality'
                                ]
                            ]
                        ],
                        'color' => [
                            'fields' => [
                                'value' => [
                                    'label' => 'Color',
                                    'placeholder' => 'Please select font color'
                                ]
                            ]
                        ],
                        'stroke' => [
                            'label' => 'Text Stroke (Outline)',
                            'fields' => [
                                'color' => [
                                    'label' => 'Color',
                                    'placeholder' => 'Please select stroke color'
                                ],
                                'width' => [
                                    'label' => 'Width',
                                    'placeholder' => 'Enter width (0 - 10)'
                                ]
                            ]
                        ],
                        'align' => [
                            'fields' => [
                                'value' => [
                                    'label' => 'Horizontal Alignment',
                                    'placeholder' => 'Please select horizontal alignment'
                                ]
                            ]
                        ],
                        'valign' => [
                            'fields' => [
                                'value' => [
                                    'label' => 'Vertical Alignment',
                                    'placeholder' => 'Please select vertical alignment'
                                ]
                            ]
                        ],
                        'angle' => [
                            'fields' => [
                                'value' => [
                                    'label' => 'Rotation Angle',
                                    'placeholder' => 'Enter rotation angle (in degrees) to rotate the image counterclockwise'
                                ]
                            ]
                        ],
                        'line_height' => [
                            'fields' => [
                                'value' => [
                                    'label' => 'Text Line Height',
                                    'placeholder' => 'Enter text line height'
                                ]
                            ]
                        ],
                        'wrap' => [
                            'label' => 'Text Wrapping',
                            'fields' => [
                                'width' => [
                                    'label' => 'Maximum Width',
                                    'placeholder' => 'Can be empty, enter the maximum width of the text block (in pixels)'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],

        // Watermark
        'watermark' => [
            'label' => 'Watermark Settings',
            'fields' => [
                'element' => [
                    'label' => 'Watermark Image',
                    'placeholder' => 'Please select watermark image'
                ],
                'position' => [
                    'label' => 'Watermark Position',
                    'placeholder' => 'Please select watermark position',
                ],
                'offset_x' => [
                    'label' => 'X-Axis Offset Pixels',
                    'placeholder' => 'Enter X-axis offset pixels',
                ],
                'offset_y' => [
                    'label' => 'Y-Axis Offset Pixels',
                    'placeholder' => 'Enter Y-axis offset pixels',
                ],
                'opacity' => [
                    'label' => 'Opacity',
                    'placeholder' => 'Image opacity, range from 0 (completely transparent) to 100 (opaque)'
                ],
                'ratio' => [
                    'label' => 'Watermark Size Ratio',
                    'placeholder' => 'Enter ratio value (0 - 100)',
                    'helper_text' => 'Enter the watermark size ratio value to dynamically calculate the watermark image size based on the uploaded image size. Calculation formula: Target image size * watermark image width or height / 100'
                ],
                'is_tiled' => [
                    'label' => 'Tile Watermark',
                    'helper_text' => 'Whether to tile the watermark across the entire image',
                ],
            ],
        ],
    ]
];