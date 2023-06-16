<?php

return [
    'billing' => [
        'name' => 'SeAT Billing',
        'icon' => 'fas fa-credit-card',
        'route_segment' => 'billing',
        'permission' => 'billing.view',
        'entries' => [
            'billing' => [
                'name' => 'Billing Data',
                'icon' => 'fas fa-money-bill',
                'route' => 'billing.view',
                'permission' => 'billing.view',
            ],
            'settings' => [
                'name' => 'Settings',
                'icon' => 'fas fa-cog',
                'route' => 'billing.settings',
                'permission' => 'billing.settings',
            ],
            'personal' => [
                'name' => 'User',
                'icon' => 'fas fa-user',
                'route' => 'billing.userBill',
            ],
            'tax' => [
                'name' => 'Tax Invoices',
                'icon' => 'fas fa-user',
                'route' => 'tax.userTaxInvoices',
            ]
        ],
    ],
];
