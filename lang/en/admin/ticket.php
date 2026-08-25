<?php

declare(strict_types=1);

return [
    'model_label' => 'Ticket',
    'plural_model_label' => 'Ticket Management',
    'columns' => [
        'issue_no' => 'Ticket Number',
        'title' => 'Title',
        'status' => 'Status',
        'level' => 'Level',
        'reply' => [
            'content' => 'Last Reply Content',
            'created_at' => 'Last Reply Time',
        ],
        'created_at' => 'Creation Time',
        'user' => [
            'name' => 'Initiating User',
            'email' => 'Initiating User Email',
            'phone' => 'Initiating User Phone Number',
        ],
    ],
    'filters' => [
        'status' => 'Status',
        'level' => 'Level',
    ],
    'actions' => [
        'close' => [
            'label' => 'Close Ticket',
            'description' => 'After closing the ticket, no further actions can be taken. Are you sure you want to close the ticket?',
            'success' => 'Closed Successfully',
        ],
        'view' => [
            'label' => 'Details'
        ],
    ],
    'view' => [
        'title' => 'Ticket Details',
        'navigation_label' => 'Ticket',
        'session_label' => 'Conversation History',
        'close_tip' => 'The ticket is closed and cannot be replied to.',
        'reply' => [
            'content' => [
                'label' => 'Reply Content',
                'placeholder' => 'Please enter reply content. Press enter for new line.',
            ],
            'submit' => [
                'label' => 'Submit Reply',
                'success' => 'Reply Submitted Successfully'
            ],
        ]
    ]
];