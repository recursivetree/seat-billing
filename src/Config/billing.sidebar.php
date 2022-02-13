<?php

return [
    'billing' => [
        'name' => 'SeAT IRS',
        'icon' => 'fa-credit-card',
        'route_segment' => 'billing',
        'permission' => 'billing.view_billing',
        'entries' => [
            'billing' => [
                'name' => 'Billing Statements',
                'icon' => 'fa-money',
                'route' => 'billing.view',
                'permission' => 'billing.view',
            ],
            'settings' => [
                'name' => 'Settings',
                'icon' => 'fa-gear',
                'route' => 'billing.settings',
                'permission' => 'billing.settings',
            ],
        ],
    ],
];
