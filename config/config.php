<?php

use Faanigee\Wallet\Models\Wallet;
use Faanigee\Wallet\Models\Transfer;
use Faanigee\Wallet\Models\Transaction;

return [

    /**
     * Base model 'transaction'.
     */
    'transaction' => [
        'table' => 'transactions',
        'model' => Transaction::class,
    ],

    /**
     * Base model 'transfer'.
     */
    'transfer' => [
        'table' => 'transfers',
        'model' => Transfer::class,
    ],

    /**
     * Base model 'wallet'.
     */
    'wallet' => [
        'table' => 'wallets',
        'model' => Wallet::class,
        'default' => [
            'name' => 'Main Wallet',
            'slug' => 'default',
            'meta' => [],
        ],
    ],
    
];
