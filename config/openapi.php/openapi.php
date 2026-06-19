<?php

return [
    /*
    |--------------------------------------------------------------------------
    | إعدادات OpenAPI لتوثيق واجهة برمجة التطبيقات
    |--------------------------------------------------------------------------
    |
    | هذا الملف يحتوي على إعدادات لتوليد توثيق OpenAPI/Swagger لواجهة برمجة
    | التطبيقات. يتم استخدام المتغيرات البيئية لجعل التكوين مرناً وقابلاً
    | للتخصيص.
    |
    */

    'api_version' => env('OPENAPI_API_VERSION', '1.0.0'),

    'title' => env('OPENAPI_TITLE', 'Omarlow API Documentation'),

    'description' => env('OPENAPI_DESCRIPTION', 'توثيق واجهة برمجة تطبيقات Omarlow القانونية التي تدعم الذكاء الاصطناعي لإدارة القضايا، العملاء، والفواتير.'),

    'servers' => [
        [
            'url' => env('APP_URL') . '/api/v1',
            'description' => 'الخادم الرئيسي لواجهة برمجة التطبيقات v1',
        ],
        // يمكن إضافة خوادم إضافية هنا للبيئات المختلفة (مثل خادم الاختبار، خادم التطوير)
    ],

    'contact' => [
        'name' => env('OPENAPI_CONTACT_NAME', 'فريق دعم Omarlow'),
        'url' => env('OPENAPI_CONTACT_URL', 'https://omarlow.com/support'),
        'email' => env('OPENAPI_CONTACT_EMAIL', 'support@omarlow.com'),
    ],

    'license' => [
        'name' => env('OPENAPI_LICENSE_NAME', 'رخصة الملكية الخاصة بـ Omarlow'),
        'url' => env('OPENAPI_LICENSE_URL', 'https://omarlow.com/license'),
    ],

    /*
    |--------------------------------------------------------------------------
    | مكونات إضافية لـ OpenAPI (اختياري)
    |--------------------------------------------------------------------------
    |
    | يمكنك هنا تعريف مخططات الأمان (security schemes)، المكونات (components)،
    | والاستجابات المشتركة (common responses) وما إلى ذلك.
    |
    */

    'security_schemes' => [
        'bearerAuth' => [
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'JWT',
            'description' => 'رمز JWT للمصادقة عبر Bearer Token',
        ],
    ],

    'components' => [
        // يمكنك إضافة تعريفات المخططات (schemas) هنا لتمثيل نماذج البيانات DTOs
        // 'schemas' => [
        //     'Error' => [
        //         'type' => 'object',
        //         'properties' => [
        //             'code' => ['type' => 'integer', 'format' => 'int32'],
        //             'message' => ['type' => 'string']
        //         ]
        //     ]
        // ],
        // يمكنك إضافة تعريفات الاستجابات (responses) المشتركة هنا
        // 'responses' => [
        //     'UnauthorizedError' => [
        //         'description' => 'مصادقة فاشلة أو غير مصرح به',
        //         'content' => [
        //             'application/json' => [
        //                 'schema' => ['ref' => '#/components/schemas/Error']
        //             ]
        //         ]
        //     ]
        // ]
    ],
];