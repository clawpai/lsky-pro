<?php

declare(strict_types=1);

return [
    'model_label' => 'User',
    'plural_model_label' => 'User Management',
    'columns' => [
        'avatar' => 'Avatar',
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone Number',
        'phone_verified_at' => 'Phone Verification Time',
        'email_verified_at' => 'Email Verification Time',
        'login_ip' => 'Last Login IP',
        'register_ip' => 'Registration IP',
        'created_at' => 'Registration Time',
        'is_admin' => 'Is Administrator',
        'photos_count' => 'Number of Photos',
        'shares_count' => 'Number of Shares',
        'orders_count' => 'Number of Orders',
        'groups_count' => 'Number of Role Groups',
        'capacities_sum_capacity' => 'Available Capacity',
        'group_name' => 'Role Group',
    ],
    'actions' => [
        'change_password' => [
            'label' => 'Change Password',
        ],
        'delete_all_photos' => [
            'label' => 'Delete All Photos',
            'modal_heading' => 'Delete All User Photos',
            'modal_description' => 'Are you sure you want to delete all photos of this user? This action cannot be undone!',
            'modal_submit' => 'Confirm Delete',
            'success_title' => 'Delete Successful',
            'success_message' => 'Successfully deleted :count photos',
            'info_title' => 'Delete Complete',
            'info_message' => 'This user has no photos to delete',
            'error_title' => 'Delete Failed',
            'error_message' => 'An error occurred while deleting photos: :error',
            'no_photos_tooltip' => 'This user has no photos',
        ]
    ],
    'filters' => [
        'email_verified' => 'Email Verification Status',
        'email_verified_true' => 'Verified',
        'email_verified_false' => 'Not Verified',
        'phone_verified' => 'Phone Verification Status',
        'phone_verified_true' => 'Verified',
        'phone_verified_false' => 'Not Verified',
        'group' => 'Role Group',
        'created_at' => 'Registration Time',
        'created_from' => 'From Date',
        'created_from_placeholder' => 'Select start date',
        'created_until' => 'To Date',
        'created_until_placeholder' => 'Select end date',
    ],
    'form_fields' => [
        'group_id' => [
            'label' => 'Role Group',
            'placeholder' => 'Please select initial role group',
        ],
        'capacity' => [
            'label' => 'Initial Capacity (KB)',
            'placeholder' => 'Please enter user initial capacity, in KB',
        ],
        'avatar' => [
            'label' => 'Avatar',
            'placeholder' => 'Select avatar',
        ],
        'username' => [
            'label' => 'Username',
            'placeholder' => 'Please enter username',
        ],
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Please enter name',
        ],
        'email' => [
            'label' => 'Email',
            'placeholder' => 'Please enter email',
        ],
        'phone' => [
            'label' => 'Phone Number',
            'placeholder' => 'Please enter phone number',
        ],
        'country_code' => [
            'label' => 'Country',
            'placeholder' => 'Please select country',
        ],
        'password' => [
            'label' => 'Password',
            'placeholder' => 'Please enter password',
            'placeholder_for_edit' => 'Leave empty if not changing',
        ],
        'new_password' => [
            'label' => 'New Password',
            'placeholder' => 'Please enter new password',
        ],
        'new_password_confirmation' => [
            'label' => 'Confirm New Password',
            'placeholder' => 'Please confirm new password',
        ],
    ],
];