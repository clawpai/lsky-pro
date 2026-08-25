<?php

declare(strict_types=1);

return [
    'model_label' => 'Violation Record',
    'plural_model_label' => 'Violation Records',
    'columns' => [
        'user' => [
            'name' => 'Username',
            'email' => 'Email',
            'phone' => 'Phone Number',
            'violations_count' => 'Number of User Violations',
        ],
        'photo' => [
            'group' => [
                'name' => 'Group Name',
            ],
            'storage' => [
                'name' => 'Storage Name',
            ],
            'thumbnail_url' => 'Thumbnail',
            'public_url' => 'Image',
            'name' => 'File Name',
            'mimetype' => 'File Type',
            'size' => 'File Size',
            'md5' => 'MD5',
            'sha1' => 'SHA-1',
            'ip_address' => 'Upload IP',
            'created_at' => 'Upload Time',
        ],
        'reason' => 'Reason for Violation',
        'status' => 'Status',
        'handled_at' => 'Handled Time',
        'created_at' => 'Record Time',
    ],
    'filters' => [
        'status' => 'Status',
        'user' => 'User',
    ],
    'actions' => [
        'handle' => [
            'label' => 'Mark as Handled',
            'success' => 'Handled Successfully',
        ],
        'delete' => [
            'label' => 'Delete',
        ],
        'delete_photo' => [
            'label' => 'Delete Photo Record',
            'modal_description' => 'Are you sure you want to delete the photo record and the physical image (excluding thumbnail) and mark it as handled?',
            'success' => 'Deleted Successfully',
        ],
        'delete_violation' => [
            'modal_description' => 'Are you sure you want to delete this violation record? If it\'s the last unhandled violation record for this image, the image status will be restored to normal.',
            'bulk_modal_description' => 'Are you sure you want to delete the selected violation records? For images with no other unhandled violation records, their status will be restored to normal.',
            'success' => 'Violation record deleted successfully',
            'error' => 'Failed to delete violation record',
            'bulk_success' => 'Successfully deleted :count violation records',
            'bulk_error_exception' => 'An error occurred while batch deleting violation records',
            'no_records_deleted' => 'No records were deleted',
        ],
    ],
];