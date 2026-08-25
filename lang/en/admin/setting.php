<?php

declare(strict_types=1);

return [
    'label' => 'System Settings',
    'title' => 'System Settings',
    'submit' => 'Save Changes',
    'saved' => 'Saved Successfully',
    'basic' => [
        'label' => 'General Settings',
        'title' => 'General Settings',
        'forms' => [
            'app' => [
                'title' => 'Application',
            ],
            'user' => [
                'title' => 'User',
            ],
            'mail' => [
                'title' => 'Mail',
            ],
            'other' => [
                'title' => 'Other',
            ],
        ],
        'fields' => [
            'name' => [
                'label' => 'Application Name',
                'placeholder' => 'Enter application name',
            ],
            'url' => [
                'label' => 'Application URL',
                'placeholder' => 'Enter application URL',
            ],
            'license_key' => [
                'label' => 'Software License key',
                'placeholder' => 'Enter software Software License key',
            ],
            'timezone' => [
                'label' => 'System Timezone',
                'placeholder' => 'Please select system timezone',
            ],
            'ip_gain_method' => [
                'label' => 'IP Acquisition Method',
                'placeholder' => 'Please select IP acquisition method',
            ],
            'currency' => [
                'label' => 'System Currency Symbol',
                'placeholder' => 'Enter system currency symbol, e.g.: CNY',
            ],
            'initial_capacity' => [
                'label' => 'User Initial Capacity',
                'placeholder' => 'Enter initial capacity for newly registered users',
            ],
            'locale' => [
                'label' => 'Default Language',
                'placeholder' => 'Please select system default language',
            ],
            'icp_no' => [
                'label' => 'ICP Number',
                'placeholder' => 'Enter ICP number',
            ],
            'image_driver' => [
                'label' => 'Image Processor',
                'placeholder' => 'Please select the default image processor of the system',
                'helper_text' => 'Note that you need to <b>manually restart the queue service</b> after the switch to make it take effect. Using the Libvips driver you must ensure that PHP has the FFI extension enabled, which is disabled by default. Switching the image processor will change the way images are handled in all locations of the system, and image formats selected in existing role groups may not be available.',
            ],
        ],
    ],
    'admin' => [
        'label' => 'Admin Settings',
        'title' => 'Admin Settings',
        'forms' => [
            'general' => [
                'title' => 'General',
            ],
        ],
        'fields' => [
            'top_navigation' => [
                'label' => 'Use Top Navigation',
            ],
            'dark_mode' => [
                'label' => 'Enable Dark Mode',
            ],
            'default_theme_mode' => [
                'label' => 'Default Theme Mode',
                'placeholder' => 'Please select default theme mode',
                'options' => [
                    'system' => 'Follow System',
                    'light' => 'Light',
                    'dark' => 'Dark',
                ],
            ],
            'primary_color' => [
                'label' => 'Primary Color',
                'placeholder' => 'Please select primary color',
            ],
        ],
    ],
    'site' => [
        'label' => 'Site Settings',
        'title' => 'Site Settings',
        'forms' => [
            'general' => [
                'title' => 'General',
            ],
            'background' => [
                'title' => 'Background Image',
            ],
            'advanced' => [
                'title' => '高级设置'
            ]
        ],
        'fields' => [
            'theme' => [
                'label' => 'Site Theme',
                'placeholder' => 'Please select site theme',
            ],
            'title' => [
                'label' => 'Site Title',
                'placeholder' => 'Enter site title',
            ],
            'subtitle' => [
                'label' => 'Site Subtitle',
                'placeholder' => 'Enter site subtitle',
            ],
            'homepage_title' => [
                'label' => 'Homepage Banner Title',
                'placeholder' => 'Enter homepage banner title',
            ],
            'homepage_description' => [
                'label' => 'Homepage Banner Description',
                'placeholder' => 'Enter homepage banner description',
            ],
            'notice' => [
                'label' => 'Popup Notice',
                'placeholder' => 'Enter popup notice. Leave empty to not display',
            ],
            'homepage_background_image_url' => [
                'label' => 'Homepage Background Image URL',
                'placeholder' => 'Enter homepage background image URL. Setting this will disable custom images',
            ],
            'homepage_background_images' => [
                'label' => 'Homepage Background Images',
                'placeholder' => 'Click to upload homepage background images',
            ],
            'auth_page_background_image_url' => [
                'label' => 'Auth Page Background Image URL',
                'placeholder' => 'Enter auth page background image URL. Setting this will disable custom images',
            ],
            'auth_page_background_images' => [
                'label' => 'Auth Page Background Images',
                'placeholder' => 'Click to upload auth page background images',
            ],
        ],
    ],
    'user' => [
        'label' => 'User Settings',
        'title' => 'User Settings',
        'forms' => [
            'general' => [
                'title' => 'General',
            ],
        ],
        'fields' => [
            'initial_capacity' => [
                'label' => 'User initial capacity',
                'placeholder' => 'Please enter the initial capacity for newly registered users',
            ],
        ]
    ],
    'control' => [
        'label' => 'Control Center',
        'title' => 'Control Center',
        'forms' => [
            'general' => [
                'title' => 'General',
            ],
        ],
        'fields' => [
            'enable_site' => [
                'label' => 'Whether or not the site is enabled',
                'helper_text' => 'Only the hybrid deployment mode is effective to control whether the site is enabled'
            ],
            'enable_registration' => [
                'label' => 'Enable Registration',
                'helper_text' => 'Enable or disable system registration feature',
            ],
            'guest_upload' => [
                'label' => 'Allow Guest Uploads',
                'helper_text' => 'Enable or disable guest file uploads. Guest uploads are controlled by the "Default System Group"',
            ],
            'user_email_verify' => [
                'label' => 'Enable Email Verification',
                'helper_text' => 'Enable or disable user account email verification feature',
            ],
            'user_phone_verify' => [
                'label' => 'Enable Phone Number Verification',
                'helper_text' => 'Enable or disable user account phone number verification feature',
            ],
            'enable_stat_api' => [
                'label' => 'Enable Statistics API',
                'helper_text' => 'Enable or disable the system statistics information API interface (Default statistics interface access address: GET /api/v2/stat{/key?) })',
            ],
            'enable_explore' => [
                'label' => 'Enable Gallery',
                'helper_text' => 'Enable or disable the explore feature. Gallery images are publicly viewable by all users'
            ],
            'enable_stat_api_key' => [
                'label' => 'Statistics API Access Key',
                'helper_text' => 'Set the access key for statistics API. Leave empty for no key verification',
                'placeholder' => 'Enter statistics API access key',
            ],
        ],
    ],
    'upgrade' => [
        'label' => 'System Upgrade',
        'title' => 'System Upgrade',
        'btn' => 'Upgrade Now',
        'status' => [
            'checking' => 'Checking...',
            'upgrading' => 'Upgrading...',
            'completed' => 'Upgrade completed. Please refresh the page.',
            'no_update' => 'Already the latest version',
        ],
        'changelog' => 'Changelog',
        'processing' => 'Processing...',
        'downloading' => 'Downloading installation package...',
        'installing' => 'Installing...',
        'server_failed' => 'Update server request failed. Please try offline installation.',
        'hash_unchanged' => 'Upgrade failed: File hash unchanged, installation package may be corrupted or upgrade process failed.',
    ],
    'queue' => [
        'label' => 'Message Queue',
        'title' => 'Message Queue',
        'tabs' => [
            'jobs' => 'Running Jobs',
            'failed_jobs' => 'Failed Jobs',
            'job_batches' => 'Job Batches',
        ],
        'queue_status' => [
            'title' => 'Queue Service Status',
            'service_status' => 'Service Status',
            'running' => 'Running',
            'stopped' => 'Stopped',
            'last_processed' => 'Last Processed',
            'oldest_pending' => 'Oldest Pending Job',
            'no_recent_activity' => 'No Recent Activity',
            'no_pending_jobs' => 'No Pending Jobs',
            'just_now' => 'Just Now',
            'minutes_ago' => ':minutes minutes ago',
        ],
        'stats' => [
            'total' => 'Total Queues',
            'pending' => 'Pending',
            'reserved' => 'Processing',
            'queues' => 'Queue Types',
        ],
        'actions' => [
            'queue_management' => 'Queue Management',
            'refresh_status' => 'Refresh Status',
            'start_queue' => 'Start Queue',
            'stop_queue' => 'Stop Queue',
            'restart_queue' => 'Restart Queue',
            'clear_all_failed' => 'Clear All Failed',
            'retry' => 'Retry',
            'view_exception' => 'View Exception',
            'delete' => 'Delete',
            'bulk_retry' => 'Bulk Retry',
            'bulk_delete' => 'Bulk Delete',
        ],
        'messages' => [
            'task_type' => 'Task Type',
            'unknown_task' => 'Unknown Task',
            'queue_exception_details' => 'Queue Exception Details',
            'failed_task_info' => 'Failed Task ID: :id | UUID: :uuid',
            'status_refreshed' => 'Status Refreshed',
            'start_queue_confirmation' => 'Are you sure you want to start the queue service in the background? This will start a daemon process to handle queue jobs.',
            'start_confirm_button' => 'Confirm Start',
            'start_cancel_button' => 'Cancel',
            'queue_start_initiated' => 'Queue Start Initiated',
            'queue_start_message' => 'Queue service start command has been executed. Please wait a moment and refresh the status to check.',
            'queue_start_failed' => 'Queue Start Failed',
            'queue_start_error' => 'Start failed: :error',
            'stop_queue_confirmation' => 'Are you sure you want to stop the queue service? This will terminate all running queue processes and jobs being processed may be interrupted.',
            'stop_confirm_button' => 'Confirm Stop',
            'stop_cancel_button' => 'Cancel',
            'queue_stop_initiated' => 'Queue Stop Initiated',
            'queue_stop_message' => 'Queue service stop command has been executed',
            'queue_stop_failed' => 'Queue Stop Failed',
            'queue_stop_error' => 'Stop failed: :error',
            'restart_queue_confirmation' => 'Are you sure you want to restart the queue service? This will gracefully stop the current queue and restart it, jobs being processed will complete before stopping.',
            'restart_confirm_button' => 'Confirm Restart',
            'restart_cancel_button' => 'Cancel',
            'queue_restart_initiated' => 'Queue Restart Initiated',
            'queue_restart_message' => 'Queue service restart command has been executed. Please wait a moment and refresh the status to check.',
            'queue_restart_failed' => 'Queue Restart Failed',
            'queue_restart_error' => 'Restart failed: :error',
            'clear_all_failed_confirmation' => 'Are you sure you want to clear all failed queue records? This operation will delete all failed job records and cannot be recovered.',
            'clear_confirm_button' => 'Confirm Clear',
            'clear_cancel_button' => 'Cancel',
            'clear_all_success' => 'Clear All Success',
            'clear_all_success_message' => 'Successfully cleared :count failed queue records',
            'clear_all_failed' => 'Clear All Failed',
            'clear_all_error' => 'Clear failed: :error',
            'no_failed_jobs' => 'No Failed Jobs',
            'no_failed_jobs_message' => 'There are no failed queue jobs to clear at the moment',
            'retry_confirmation' => 'Are you sure you want to retry this failed queue job? The job will be re-queued.',
            'retry_confirm_button' => 'Confirm Retry',
            'retry_cancel_button' => 'Cancel',
            'retry_success' => 'Retry Success',
            'retry_success_message' => 'Failed queue job #:id has been re-queued',
            'retry_failed' => 'Retry Failed',
            'retry_failed_message' => 'Retry failed: :error',
            'bulk_retry_confirmation' => 'Are you sure you want to retry the selected failed queue jobs? The jobs will be re-queued.',
            'bulk_retry_success' => 'Bulk Retry Completed',
            'bulk_retry_success_message' => 'Successfully retried :success jobs, failed :failed jobs',
            'bulk_retry_failed' => 'Bulk Retry Failed',
            'bulk_retry_failed_message' => 'Retry failed for :count jobs',
            'delete_confirmation' => 'Are you sure you want to delete this failed queue record? This action cannot be undone.',
            'delete_confirm_button' => 'Confirm Delete',
            'delete_cancel_button' => 'Cancel',
            'delete_success' => 'Delete Success',
            'delete_success_message' => 'Failed queue record #:id has been deleted',
            'bulk_delete_confirmation' => 'Are you sure you want to delete the selected failed queue records? This action cannot be undone.',
            'bulk_delete_success' => 'Bulk Delete Success',
            'bulk_delete_success_message' => 'Successfully deleted :count failed queue records',
            'no_name_batch' => 'Unnamed Batch',
            'not_finished' => 'Not Finished',
            'warning_message' => '⚠️ Please ensure that the queuing service is operating normally; otherwise, it will affect the system\'s functions such as generating thumbnails, sending emails, and deleting pictures You can use php artisan queue:work to start queue processing.',
            'basic_info' => 'Basic Information',
            'payload_info' => 'Payload Information',
            'exception_details' => 'Exception Details',
        ],
        'fields' => [
            'id' => 'ID',
            'queue' => 'Queue Name',
            'attempts' => 'Attempts',
            'payload' => 'Payload',
            'created_at' => 'Created At',
            'available_at' => 'Available At',
            'reserved_at' => 'Reserved At',
            'uuid' => 'UUID',
            'connection' => 'Connection',
            'exception' => 'Exception',
            'failed_at' => 'Failed At',
            'name' => 'Batch Name',
            'total_jobs' => 'Total Jobs',
            'pending_jobs' => 'Pending Jobs',
            'failed_jobs' => 'Failed Jobs',
            'finished_at' => 'Finished At',
            'cancelled_at' => 'Cancelled At',
        ],
    ],
];