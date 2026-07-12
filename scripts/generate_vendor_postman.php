<?php

/**
 * Generate docs/postman/KWEEK_Vendor_API.postman_collection.json with all vendor routes.
 * Run: php scripts/generate_vendor_postman.php
 */

$output = __DIR__ . '/../docs/postman/KWEEK_Vendor_API.postman_collection.json';

$tokenScript = [
    'if (pm.response.code === 200 || pm.response.code === 201) {',
    '  var json = pm.response.json();',
    "  if (json.token) pm.collectionVariables.set('token', json.token);",
    '}',
];

$orderIdScript = [
    'var json = pm.response.json();',
    "if (json.data && json.data.data && json.data.data.length) pm.collectionVariables.set('order_id', json.data.data[0].id);",
    "else if (json.data && json.data.length) pm.collectionVariables.set('order_id', json.data[0].id);",
];

$productIdScript = [
    'var json = pm.response.json();',
    "if (json.data && json.data.id) pm.collectionVariables.set('product_id', json.data.id);",
];

$driverIdScript = [
    'var json = pm.response.json();',
    "if (json.data && json.data.driver && json.data.driver.id) pm.collectionVariables.set('driver_id', json.data.driver.id);",
    "else if (json.data && json.data.id) pm.collectionVariables.set('driver_id', json.data.id);",
];

$couponIdScript = [
    'var json = pm.response.json();',
    "if (json.data && json.data.id) pm.collectionVariables.set('coupon_id', json.data.id);",
];

$adIdScript = [
    'var json = pm.response.json();',
    "if (json.data && json.data.id) pm.collectionVariables.set('advertisement_id', json.data.id);",
];

function req(string $name, string $method, string $path, array $opts = []): array
{
    $item = [
        'name' => $name,
        'request' => [
            'method' => $method,
            'header' => [['key' => 'Accept', 'value' => 'application/json']],
            'url' => '{{base_url}}/api/vendor' . $path,
        ],
    ];

    if (! empty($opts['noauth'])) {
        $item['request']['auth'] = ['type' => 'noauth'];
    }

    if (! empty($opts['body'])) {
        $item['request']['header'][] = ['key' => 'Content-Type', 'value' => 'application/json'];
        $item['request']['body'] = [
            'mode' => 'raw',
            'raw' => json_encode($opts['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'options' => ['raw' => ['language' => 'json']],
        ];
    }

    if (! empty($opts['form'])) {
        $item['request']['body'] = [
            'mode' => 'formdata',
            'formdata' => $opts['form'],
        ];
    }

    if (! empty($opts['test'])) {
        $item['event'] = [['listen' => 'test', 'script' => ['exec' => $opts['test']]]];
    }

    if (! empty($opts['description'])) {
        $item['request']['description'] = $opts['description'];
    }

    return $item;
}

$collection = [
    'info' => [
        'name' => 'KWEEK Vendor API',
        'description' => 'Complete Vendor/Store APIs for emart_vendor Flutter app — all routes from routes/api.php vendor prefix.',
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
    ],
    'variable' => [
        ['key' => 'base_url', 'value' => 'http://127.0.0.1:8000'],
        ['key' => 'token', 'value' => ''],
        ['key' => 'section_id', 'value' => '6285dcf511651'],
        ['key' => 'order_id', 'value' => ''],
        ['key' => 'product_id', 'value' => ''],
        ['key' => 'coupon_id', 'value' => ''],
        ['key' => 'driver_id', 'value' => ''],
        ['key' => 'advertisement_id', 'value' => ''],
        ['key' => 'booking_id', 'value' => ''],
    ],
    'auth' => [
        'type' => 'bearer',
        'bearer' => [['key' => 'token', 'value' => '{{token}}', 'type' => 'string']],
    ],
    'item' => [
        [
            'name' => 'Auth',
            'item' => [
                req('Register', 'POST', '/register', [
                    'noauth' => true,
                    'body' => [
                        'firstName' => 'Raj',
                        'lastName' => 'Store',
                        'email' => 'vendor@example.com',
                        'phoneNumber' => '9999999999',
                        'password' => 'password123',
                        'password_confirmation' => 'password123',
                        'sectionId' => '{{section_id}}',
                    ],
                    'test' => $tokenScript,
                ]),
                req('Login', 'POST', '/login', [
                    'noauth' => true,
                    'body' => [
                        'email' => 'vendor@example.com',
                        'password' => 'password123',
                        'fcmToken' => '',
                    ],
                    'test' => $tokenScript,
                ]),
                req('Login with Google', 'POST', '/auth/google', [
                    'noauth' => true,
                    'body' => ['id_token' => 'GOOGLE_ID_TOKEN', 'fcmToken' => ''],
                ]),
                req('Login with Apple', 'POST', '/auth/apple', [
                    'noauth' => true,
                    'body' => ['id_token' => 'APPLE_ID_TOKEN', 'fcmToken' => ''],
                ]),
                req('Login with Phone', 'POST', '/auth/phone', [
                    'noauth' => true,
                    'body' => [
                        'phoneNumber' => '9999999999',
                        'countryCode' => '+91',
                        'auto_register' => true,
                        'fcmToken' => '',
                    ],
                    'test' => $tokenScript,
                ]),
                req('Forgot Password', 'POST', '/password/forgot', [
                    'noauth' => true,
                    'body' => ['email' => 'vendor@example.com'],
                ]),
                req('Reset Password', 'POST', '/password/reset', [
                    'noauth' => true,
                    'body' => [
                        'email' => 'vendor@example.com',
                        'token' => 'RESET_TOKEN_FROM_EMAIL',
                        'password' => 'newpassword123',
                        'password_confirmation' => 'newpassword123',
                    ],
                ]),
                req('Logout', 'POST', '/logout'),
                req('Delete Account', 'DELETE', '/account'),
            ],
        ],
        [
            'name' => 'Public Content',
            'item' => [
                req('Home', 'GET', '/home', ['noauth' => true]),
                req('Terms', 'GET', '/terms', ['noauth' => true]),
                req('Privacy', 'GET', '/privacy', ['noauth' => true]),
                req('Catalog', 'GET', '/catalog', ['noauth' => true]),
                req('Catalog by Section', 'GET', '/catalog?sectionId={{section_id}}', ['noauth' => true]),
                req('Subscription Plans', 'GET', '/subscriptions/plans', ['noauth' => true]),
                req('Subscription Plans by Section', 'GET', '/subscriptions/plans?sectionId={{section_id}}', ['noauth' => true]),
            ],
        ],
        [
            'name' => 'Profile & Store',
            'item' => [
                req('Get Profile', 'GET', '/profile'),
                req('Update Profile', 'PUT', '/profile', [
                    'body' => [
                        'firstName' => 'Raj',
                        'lastName' => 'Store',
                        'phoneNumber' => '9999999999',
                        'countryCode' => '+91',
                        'fcmToken' => '',
                    ],
                ]),
                req('Upload Profile Image', 'POST', '/profile/image', [
                    'form' => [
                        ['key' => 'image', 'type' => 'file', 'src' => ''],
                    ],
                    'description' => 'Multipart form-data with image file field.',
                ]),
                req('Update Bank Details', 'PUT', '/bank-details', [
                    'body' => [
                        'userBankDetails' => [
                            'accountNumber' => '1234567890',
                            'bankName' => 'Test Bank',
                            'holderName' => 'Raj Store',
                        ],
                    ],
                ]),
                req('Get Store', 'GET', '/store'),
                req('Create Store', 'POST', '/store', [
                    'body' => [
                        'title' => 'My Restaurant',
                        'description' => 'Best food in town',
                        'latitude' => 12.9716,
                        'longitude' => 77.5946,
                        'isSelfDelivery' => true,
                        'dine_in_active' => false,
                    ],
                ]),
                req('Update Store', 'PUT', '/store', [
                    'body' => [
                        'description' => 'Updated description',
                        'workingHours' => [],
                        'specialDiscountEnable' => false,
                    ],
                ]),
                req('Upload Store Image', 'POST', '/store/image', [
                    'form' => [
                        ['key' => 'image', 'type' => 'file', 'src' => ''],
                        ['key' => 'type', 'value' => 'photo', 'type' => 'text'],
                    ],
                ]),
                req('Dashboard', 'GET', '/dashboard'),
            ],
        ],
        [
            'name' => 'Orders',
            'item' => [
                req('List All Orders', 'GET', '/orders'),
                req('List New Orders', 'GET', '/orders?tab=new', ['test' => $orderIdScript]),
                req('List Active Orders', 'GET', '/orders?tab=active'),
                req('List Completed Orders', 'GET', '/orders?tab=completed'),
                req('List Cancelled Orders', 'GET', '/orders?tab=cancelled'),
                req('Order Detail', 'GET', '/orders/{{order_id}}'),
                req('Accept Order', 'POST', '/orders/{{order_id}}/accept', [
                    'body' => ['estimatedTimeToPrepare' => '30'],
                ]),
                req('Reject Order', 'POST', '/orders/{{order_id}}/reject', [
                    'body' => ['reason' => 'Out of stock'],
                ]),
                req('Cancel Order', 'POST', '/orders/{{order_id}}/cancel', [
                    'body' => ['reason' => 'Cancelled by vendor'],
                ]),
                req('Complete Order', 'POST', '/orders/{{order_id}}/complete'),
                req('Assign Driver', 'POST', '/orders/{{order_id}}/assign-driver', [
                    'body' => ['driverId' => '{{driver_id}}'],
                ]),
                req('Ship Order', 'POST', '/orders/{{order_id}}/ship', [
                    'body' => [
                        'courierCompanyName' => 'BlueDart',
                        'courierTrackingId' => 'BD123456789',
                    ],
                ]),
                req('Update Order', 'PATCH', '/orders/{{order_id}}', [
                    'body' => ['notes' => 'Updated via API'],
                ]),
            ],
        ],
        [
            'name' => 'Products',
            'item' => [
                req('List Products', 'GET', '/products'),
                req('Create Product', 'POST', '/products', [
                    'body' => [
                        'name' => 'Margherita Pizza',
                        'description' => 'Classic pizza',
                        'price' => 299,
                        'publish' => true,
                        'veg' => true,
                    ],
                    'test' => $productIdScript,
                ]),
                req('Product Detail', 'GET', '/products/{{product_id}}'),
                req('Update Product', 'PUT', '/products/{{product_id}}', [
                    'body' => ['name' => 'Updated Pizza', 'price' => 349],
                ]),
                req('Delete Product', 'DELETE', '/products/{{product_id}}'),
                req('Upload Product Images', 'POST', '/products/{{product_id}}/images', [
                    'form' => [
                        ['key' => 'images[]', 'type' => 'file', 'src' => ''],
                    ],
                ]),
            ],
        ],
        [
            'name' => 'Coupons',
            'item' => [
                req('List Coupons', 'GET', '/coupons'),
                req('Create Coupon', 'POST', '/coupons', [
                    'body' => [
                        'code' => 'SAVE10',
                        'discount' => 10,
                        'discountType' => 'Percentage',
                        'expiresAt' => '2026-12-31',
                    ],
                    'test' => $couponIdScript,
                ]),
                req('Coupon Detail', 'GET', '/coupons/{{coupon_id}}'),
                req('Update Coupon', 'PUT', '/coupons/{{coupon_id}}', [
                    'body' => ['discount' => 15],
                ]),
                req('Delete Coupon', 'DELETE', '/coupons/{{coupon_id}}'),
                req('Upload Coupon Image', 'POST', '/coupons/{{coupon_id}}/image', [
                    'form' => [
                        ['key' => 'image', 'type' => 'file', 'src' => ''],
                    ],
                ]),
            ],
        ],
        [
            'name' => 'Wallet',
            'item' => [
                req('Balance', 'GET', '/wallet'),
                req('Transactions', 'GET', '/wallet/transactions'),
                req('Earnings', 'GET', '/earnings'),
                req('Payout History', 'GET', '/wallet/payouts'),
                req('Withdraw', 'POST', '/wallet/withdraw', [
                    'body' => ['amount' => 100, 'withdrawMethod' => 'bank'],
                ]),
                req('Get Withdraw Method', 'GET', '/withdraw-method'),
                req('Save Withdraw Method', 'PUT', '/withdraw-method', [
                    'body' => [
                        'flutterWave' => ['accountNumber' => '123', 'bankCode' => '044'],
                        'stripe' => ['accountId' => 'acct_xxx'],
                    ],
                ]),
            ],
        ],
        [
            'name' => 'Chat',
            'item' => [
                req('Customer Inbox', 'GET', '/chat/inbox?type=customer'),
                req('Admin Inbox', 'GET', '/chat/inbox?type=admin'),
                req('Messages', 'GET', '/chat/{{order_id}}/messages?type=customer'),
                req('Send Message', 'POST', '/chat/send', [
                    'body' => [
                        'orderId' => '{{order_id}}',
                        'message' => 'Hello from vendor',
                        'type' => 'customer',
                    ],
                ]),
                req('Upload Chat Media', 'POST', '/chat/upload', [
                    'form' => [
                        ['key' => 'file', 'type' => 'file', 'src' => ''],
                        ['key' => 'mediaType', 'value' => 'image', 'type' => 'text'],
                    ],
                ]),
            ],
        ],
        [
            'name' => 'Reviews',
            'item' => [
                req('List Reviews', 'GET', '/reviews'),
                req('Review for Order', 'GET', '/reviews/order/{{order_id}}'),
                req('Ratings', 'GET', '/ratings'),
            ],
        ],
        [
            'name' => 'Drivers',
            'item' => [
                req('List Drivers', 'GET', '/drivers'),
                req('Create Driver', 'POST', '/drivers', [
                    'body' => [
                        'firstName' => 'Delivery',
                        'lastName' => 'Boy',
                        'email' => 'driver.store@example.com',
                        'phoneNumber' => '8888888888',
                        'carNumber' => 'KA01XY9999',
                    ],
                    'test' => $driverIdScript,
                ]),
                req('Driver Detail', 'GET', '/drivers/{{driver_id}}'),
                req('Update Driver', 'PUT', '/drivers/{{driver_id}}', [
                    'body' => ['carNumber' => 'KA01ZZ1234'],
                ]),
                req('Upload Driver Image', 'POST', '/drivers/{{driver_id}}/image', [
                    'form' => [
                        ['key' => 'image', 'type' => 'file', 'src' => ''],
                    ],
                ]),
            ],
        ],
        [
            'name' => 'Dine-In',
            'item' => [
                req('List Bookings', 'GET', '/dine-in/bookings'),
                req('Upcoming Bookings', 'GET', '/dine-in/bookings?tab=upcoming'),
                req('Past Bookings', 'GET', '/dine-in/bookings?tab=past'),
                req('Booking Detail', 'GET', '/dine-in/bookings/{{booking_id}}'),
                req('Accept Booking', 'POST', '/dine-in/bookings/{{booking_id}}/accept'),
                req('Reject Booking', 'POST', '/dine-in/bookings/{{booking_id}}/reject', [
                    'body' => ['reason' => 'Fully booked'],
                ]),
                req('Update Dine-In Config', 'PUT', '/dine-in/config', [
                    'body' => [
                        'dine_in_active' => true,
                        'openDineTime' => '10:00',
                        'closeDineTime' => '22:00',
                        'restaurantCost' => '500',
                    ],
                ]),
            ],
        ],
        [
            'name' => 'Subscriptions',
            'item' => [
                req('Subscription History', 'GET', '/subscriptions/history'),
                req('Subscribe', 'POST', '/subscriptions', [
                    'body' => [
                        'plan_id' => 'PLAN_ID_HERE',
                        'payment_type' => 'razorpay',
                        'payment_status' => 'success',
                    ],
                ]),
            ],
        ],
        [
            'name' => 'Advertisements',
            'item' => [
                req('List Advertisements', 'GET', '/advertisements'),
                req('Create Advertisement', 'POST', '/advertisements', [
                    'body' => [
                        'title' => 'Summer Sale',
                        'description' => '50% off',
                        'type' => 'restaurant_promotion',
                    ],
                    'test' => $adIdScript,
                ]),
                req('Advertisement Detail', 'GET', '/advertisements/{{advertisement_id}}'),
                req('Update Advertisement', 'PUT', '/advertisements/{{advertisement_id}}', [
                    'body' => ['title' => 'Updated Sale'],
                ]),
                req('Delete Advertisement', 'DELETE', '/advertisements/{{advertisement_id}}'),
                req('Upload Advertisement Media', 'POST', '/advertisements/{{advertisement_id}}/media', [
                    'form' => [
                        ['key' => 'file', 'type' => 'file', 'src' => ''],
                        ['key' => 'type', 'value' => 'profile', 'type' => 'text'],
                    ],
                ]),
            ],
        ],
        [
            'name' => 'Story',
            'item' => [
                req('Get Story', 'GET', '/story'),
                req('Create/Update Story', 'POST', '/story', [
                    'body' => [
                        'videoUrl' => ['https://example.com/video.mp4'],
                        'thumbnailUrl' => 'https://example.com/thumb.jpg',
                    ],
                ]),
                req('Delete Story', 'DELETE', '/story'),
                req('Upload Story Media', 'POST', '/story/upload', [
                    'form' => [
                        ['key' => 'file', 'type' => 'file', 'src' => ''],
                        ['key' => 'mediaType', 'value' => 'image', 'type' => 'text'],
                    ],
                ]),
            ],
        ],
        [
            'name' => 'Documents & Notifications',
            'item' => [
                req('List Documents', 'GET', '/documents'),
                req('Document Status', 'GET', '/documents/status'),
                req('Submit Documents', 'POST', '/documents', [
                    'body' => ['documents' => []],
                ]),
                req('Upload Document', 'POST', '/documents/upload', [
                    'form' => [
                        ['key' => 'file', 'type' => 'file', 'src' => ''],
                        ['key' => 'side', 'value' => 'front', 'type' => 'text'],
                    ],
                ]),
                req('Notifications', 'GET', '/notifications'),
            ],
        ],
    ],
];

$json = json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
file_put_contents($output, $json . "\n");

$count = 0;
foreach ($collection['item'] as $folder) {
    $count += count($folder['item']);
}

echo "Generated {$output}\n";
echo "Folders: " . count($collection['item']) . ", Requests: {$count}\n";
