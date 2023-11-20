<?php

namespace Faanigee\Wallet\Http\Traits;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Faanigee\Wallet\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Faanigee\Wallet\Helpers\Helper;
use Faanigee\Wallet\Models\Transaction;

trait WalletManager
{
  public function deposit(User $user = null, int|float $amount, bool $confirmed = false, ?array $meta = null)
  {
    try {
      DB::beginTransaction();
      $currentUser = $user ?? auth()->user();
      // dd($currentUser);
      // dd($user, $amount, $confirmed, $meta);
      $user_id = $currentUser->id;
      $result = $this->testUserAthenticity($user_id);

      if ($result['success'] === true) {
        // dd($currentUser);
        $wallet = $currentUser->wallet;
        if ($wallet) {
          $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
          $balance = $wallet->getBalance();

          $transaction = Transaction::create([
            'wallet_id' => $wallet->id,
            'payable_type' => 'App\User',
            'payable_id' => $user_id,
            'type' => 'deposit',
            'holder_id' => $user_id,
            'amount' => $amount,
            'meta' => $meta,
            'confirmed' => $confirmed,
            'uuid' => Str::uuid(),
          ]);

          if ($transaction && $confirmed) {
            $balance = $wallet->getBalance();
            $wallet->balance = $balance + (int)$amount;
            $wallet->update();
          }
          DB::commit();
          $user = null;
          return Helper::ajaxResponse($transaction->toArray(), 200, "User: $currentUser->name, Wallet ID: $wallet->id, Deposite ($amount), Transaction Successfull...");
        } else {

          DB::commit();
          return Helper::ajaxResponse($result, 302, 'wallet is not exist...!');
        }

        // dd($balance, $amount);

        // return $transaction;

      } else {
        DB::rollBack();
        return Helper::ajaxResponse($result, 302, 'wallet is Banned or not exist...!');
      }
    } catch (Exception $e) {
      DB::rollBack();
      return Helper::ajaxResponse($e->getTrace(), 302, "Exception(WM-69): " . $e->getMessage());
    }
  }

  protected function withdraw(User $user = null, int|float $amount, bool $confirmed = false, ?array $meta = null)
  {
    try {
      DB::beginTransaction();
      $currentUser = $user ?? User::find(auth()->user())->with('wallet')->first();

      $user_id = $currentUser->id;
      $result = $this->testUserAthenticity($user_id);

      if ($result['success'] === true) {
        $wallet = $currentUser->wallet;

        if ($wallet) {
          $balance = $wallet->getBalance();

          if ($balance < 0) {
            $wallet->status = 'Banned';
            $wallet->update();
            DB::commit();
            $user = null;
            return Helper::ajaxResponse($wallet, 302, "User Banned due to Negative Balance...");
          }
          if ($balance < $amount || $balance == 0) {
            $reqBalance = $amount - $balance;
            return Helper::ajaxResponse($wallet->toArray(), 302, "Low Balance (current: $balance, required more: $reqBalance)...!");
          }

          $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

          $transaction = Transaction::create([
            'wallet_id' => $wallet->id,
            'payable_type' => 'App\User',
            'payable_id' => $user_id,
            'type' => 'withdraw',
            'holder_id' => $user_id,
            'amount' => -$amount,
            'meta' => $meta,
            'confirmed' => $confirmed,
            'uuid' => Str::uuid(),
          ]);

          if ($transaction && $confirmed) {
            $balance = $wallet->getBalance();
            $wallet->balance = $balance - (int)$amount;
            $wallet->update();
          }
          DB::commit();
          $user = null;
          return Helper::ajaxResponse($transaction->toArray(), 200, "User: $currentUser->name, Wallet ID: $wallet->id, Withdrawal ($amount), Transaction Successfull...");
        }
        // return $transaction;
        else {
          DB::commit();
          $user = null;
          return Helper::ajaxResponse($result, 302, 'wallet is not exist...!');
        }
      } else {
        DB::rollBack();
        return Helper::ajaxResponse($result, 302, 'wallet is Banned or not exist...!');
      }
    } catch (Exception $e) {
      DB::rollBack();
      return Helper::ajaxResponse($e->getTrace(), 302, "Exception(WM-126): " . $e->getMessage());
    }
  }

  protected function updateBalance($wallet_id)
  {
    DB::beginTransaction();
    $wallet = Wallet::find($wallet_id);
    $userName = User::find($wallet->holder_id)->name;
    $deposit = Transaction::where('wallet_id', $wallet_id)->where('payable_id', $wallet->holder_id)->where('type', 'deposit')->where('confirmed', 1)->sum('amount');
    $withdraw = Transaction::where('wallet_id', $wallet_id)->where('payable_id', $wallet->holder_id)->where('type', 'withdraw')->where('confirmed', 1)->sum('amount');

    $balance = $deposit - abs($withdraw);

    DB::commit();
    return Helper::ajaxResponse($balance, 200, "User: $userName, Wallet ID: $wallet->id, Deposite ($deposit), Withdraw ($withdraw), Actual Wallet Balance: ($wallet->balance)");
  }

  protected function testUserAthenticity($id)
  {
    if ($id) {
      $userStatus = $this->CheckUserStatus($id);
      $userWalletStatus = $this->checkUserWallet($id);
      $data = [
        'user' => $userStatus,
        'wallet' => $userWalletStatus,
      ];
      if ($userStatus && $userWalletStatus) {
        return Helper::ajaxResponse($data, 200, 'Wallet And User is Authenticated Successfully...');
      } elseif ($userStatus) {
        return Helper::ajaxResponse($data, 302, 'wallet is Banned or not exist...!');
      } else {
        return Helper::ajaxResponse($data, 302, 'User is Banned or not exist...!');
      }
    } else {
      return Helper::ajaxResponse(null, 302, 'Empty Parameter for processing...!');
    }
  }

  protected function CheckUserStatus($id)
  {
    $checkUser = User::where('id', $id)
      ->first();
    if ($checkUser) {
      return $checkUser->toArray();
    } else {
      return false;
    }
  }

  protected function checkUserWallet($id)
  {
    $checkWallet = Wallet::where('holder_id', $id)->first();
    if ($checkWallet) {
      if ($checkWallet->status === 'Active')
        return $checkWallet->toArray();
      else {
        return response(['status' => $checkWallet->status], 201);
      }
    } else {
      $wallet = $this->getOrCreateWallet($id);
      return $wallet->toArray();
    }
  }

  protected function getOrCreateWallet($id)
  {
    $defaultSlug = config('wallet.wallet.default.slug', 'default');
    // dd(config('wallet.wallet.default.slug'));
    $model = new Wallet();
    // Attempt to retrieve the existing wallet
    $wallet = $model
      ->morphOne(Wallet::class, 'holder')
      ->where('slug', $defaultSlug)
      ->where('holder_id', $id)
      ->first();

    // dd($wallet);
    // If the wallet doesn't exist, create a new one
    if (!$wallet) {
      $wallet = new Wallet([
        'name' => config('wallet.wallet.default.name', 'Default Wallet'),
        'slug' => $defaultSlug,
        'holder_type' => 'App\User',
        'uuid' => Str::uuid(),
        'holder_id' => $id,
        'meta' => config('wallet.wallet.default.meta', []),
        'balance' => 0,
      ]);

      // dd($wallet);

      // Associate the wallet with the model
      $wallet->save(); // Assuming $this is an instance of YourModel
      // $model->wallet()->save($wallet); // Assuming 'wallet' is the relation name
    }

    return $wallet;
  }

  protected function compareTransactions($id)
  {
    $wallet = Wallet::find($id);
    $userName = User::find($wallet->holder_id)->name;
    $deposit = Transaction::where('wallet_id', $id)->where('payable_id', $wallet->holder_id)->where('type', 'deposit')->where('confirmed', 1)->sum('amount');
    $withdraw = Transaction::where('wallet_id', $id)->where('payable_id', $wallet->holder_id)->where('type', 'withdraw')->where('confirmed', 1)->sum('amount');

    $balance = $deposit - abs($withdraw);

    if ((int) $wallet->balance === (int) $balance)
      return Helper::ajaxResponse($balance, 200, "User: $userName, Wallet ID: $wallet->id, Deposite ($deposit), Withdraw ($withdraw), Actual Wallet Balance: ($wallet->balance)");
    else
      return Helper::ajaxResponse($balance, 302, "User: $userName, Wallet ID: $wallet->id, Deposite ($deposit), Withdraw ($withdraw), Actual Wallet Balance: ($wallet->balance)");
  }

  public function wallet()
  {
    return $this->hasOne(Wallet::class, 'holder_id', 'id');
  }
}
