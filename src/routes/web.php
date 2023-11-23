<?php

use Faanigee\Wallet\Http\Controllers\WalletController;

Route::get('/wallet', function () {
  return view('wallet::index')->with(['config' => config('wallet')]);
});

Route::get('/wallet/test', [WalletController::class, 'test']);
Route::match(['get', 'post'], '/wallet/ledger', [WalletController::class, 'walletReport']);
Route::match(['get', 'post'], '/wallet/defaulters', [WalletController::class, 'checkDefaulters']);
