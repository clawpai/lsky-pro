<?php

declare(strict_types=1);

return [
    'model_label' => 'Image',
    'plural_model_label' => 'Image Management',
    'columns' => [
        'user' => [
            'avatar' => 'Avatar',
            'name' => 'Username',
            'email' => 'Email',
            'phone' => 'Phone Number',
        ],
        'group' => [
            'name' => 'Group Name',
        ],
        'storage' => [
            'name' => 'Storage Name',
        ],
        'thumbnail_url' => 'Thumbnail',
        'public_url' => 'Image',
        'name' => 'Image Name',
        'mimetype' => 'Image Type',
        'size' => 'Image Size',
        'is_public' => 'Is Public',
        'status' => 'Status',
        'md5' => 'MD5',
        'sha1' => 'SHA-1',
        'ip_address' => 'Upload IP',
        'created_at' => 'Upload Time',
    ],
    'filters' => [
        'status' => 'Status',
        'user' => 'User',
        'storage' => 'Storage',
        'is_public' => 'Visibility Status',
        'is_public_true' => 'Public',
        'is_public_false' => 'Private',
    ],
    'actions' => [
        'rename' => [
            'label' => 'Rename',
            'form_fields' => [
                'name' => 'Image Name',
            ],
            'success' => 'Rename Successful',
        ],
        'url' => [
            'label' => 'Open Image',
        ],
        'restore_violation' => [
            'label' => 'Restore Violation Image',
            'modal_description' => 'Are you sure you want to restore this violation image to normal status? This will mark all related violation records as handled.',
            'bulk_modal_description' => 'Are you sure you want to restore the selected violation images to normal status? This will mark all related violation records as handled.',
            'success' => 'Violation image restored successfully',
            'error' => 'Failed to restore violation image',
            'bulk_success' => 'Successfully restored :count violation images',
            'bulk_error' => 'Failed to restore :count images',
            'no_violation_selected' => 'No violation images selected',
        ],
        'update_status' => [
            'success' => 'Status updated successfully',
            'error' => 'Failed to update status',
        ],
    ],
    'status' => [
        'normal' => 'Normal',
        'pending' => 'Pending',
        'violation' => 'Violation',
    ],
    'normal' => 'Normal',
    'violation' => 'Violation of rules',
];