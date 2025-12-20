<?php

return [

    'single' => [

        'label' => 'Ẩn',

        'modal' => [

            'heading' => 'Ẩn :label',

            'actions' => [

                'delete' => [
                    'label' => 'Ẩn',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Đã Ẩn',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Xoá các mục đã chọn',

        'modal' => [

            'heading' => 'Ẩn các mục :label đã chọn',

            'actions' => [

                'delete' => [
                    'label' => 'Ẩn',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Đã Ẩn',
            ],

            'deleted_partial' => [
                'title' => 'Đã Ẩn :count trong tổng số :total mục',
                'missing_authorization_failure_message' => 'Bạn không có quyền Ẩn :count mục.',
                'missing_processing_failure_message' => 'Đã không thể Ẩn :count mục.',
            ],

            'deleted_none' => [
                'title' => 'Việc Ẩn đã thất bại',
                'missing_authorization_failure_message' => 'Bạn không có quyền Ẩn :count mục.',
                'missing_processing_failure_message' => 'Đã không thể Ẩn :count mục.',
            ],

        ],

    ],

];
