<?php

declare(strict_types=1);

return [
    'model_label' => 'Suggestions and Feedback',
    'plural_model_label' => 'Suggestions and Feedback',
    'columns' => [
        'title' => 'Title',
        'name' => 'Name',
        'email' => 'Email',
        'type' => 'Type',
        'content' => 'Feedback Content',
        'ip_address' => 'IP Address',
        'created_at' => 'Feedback Time',
    ],
    'actions' => [],
    'filters' => [
        'type' => 'Type',
    ],
];