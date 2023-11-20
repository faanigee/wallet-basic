<?php

namespace Faanigee\Wallet\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Faanigee\Wallet\Models\Wallet;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Faanigee\Wallet\Models\Transaction;
use Faanigee\Wallet\Http\Traits\WalletManager;

// use Modules\Accounts\Jobs\TestWalletJob;

class WalletController extends Controller
{
  use WalletManager;
  public function test()
  {

    $user = User::where('id', 1)->with('wallet')->first();
    // dd($user);
    $res_deposit = $this->deposit($user, 10000, true, ['description' => 'testing amount', 'working' => 'ok']);
    $res_withdraw = $this->withdraw($user, 2500, true, ['description' => 'testing amount', 'working' => 'ok']);
    // $balance = $this->updateBalance(1);

    // dd($balance);
    // dd($res_deposit, $balance);
    dd($res_deposit ?? 'Deposite Not Found', $res_withdraw ?? 'Withrawal Not Found');

    // $user = User::find(1); // Replace with your actual user
    // $amount = 1500; // Replace with your desired amount

    // // Dispatch multiple jobs concurrently
    // for ($i = 0; $i < 10; $i++) {
    //   $data[] = TestWalletJob::dispatch($user, $amount, true, ['key' => 'value']);
    // }

    // dd($data);
  }

  public function walletReport(Request $request)
  {
    $user = $request->userId;
    $user = $user ? $user : ($user ? Auth::user() : User::find(1)->with('wallet')->first());
    // dd($user);
    if ($user) {
      $wallet = $user->wallet;
      if ($wallet) {
        $balance = 0;
        $ledger = Transaction::where('wallet_id', $wallet->id)->get();
        return view('wallet::ledger', compact('ledger', 'user', 'balance'));
        // return response($ledger, 200);
      } else {
        return response('Wallet is not associated with this User: ' . $user->irfan, 201);
      }
    } else {
      return response('Please Select the User to get the report', 201);
    }
  }

  public function checkDefaulters()
  {

    $data = [];
    $wallets = Wallet::where('balance', '<', 0)->get();
    foreach (Wallet::where('status', 'Active')->where('first_run', 0)->limit(5000)->latest()->get() as $wallet) {
      if ($wallet) {
        $faulter = $this->compareTransactions($wallet);
        if ($faulter['success'] === false && $faulter['data'] != 1) {
          $data[] = [
            'holder_id' => $faulter['data'][0]['holder_id'],
            'id' => $faulter['data'][0]['id'],
            'wallet_balance' => $faulter['data'][0]['balance'],
            'transactions_balance' => $faulter['data'][1],
            'status' => $faulter['data'][0]['status'],
          ];
          // dd($data);
        }
        $wallet->first_run = 1;
        $wallet->update();
      }
    }

    if ($wallets) {
      return view('wallet::defaulter', compact('wallets', 'data'));
    } else {
      return response('No Wallet Found with Negative Balance');
    }
  }
}
