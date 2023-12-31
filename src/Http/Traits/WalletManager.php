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
  public function deposit($user = null, int|float $amount, ?array $meta = null, $ref_id = null, bool $confirmed = true)
  {
    try {
      DB::beginTransaction();
      $currentUser = $user ?? auth()->user();
      //   $currentUser->refresh();
      // dd($currentUser);
      // dd($user, $amount, $confirmed, $meta);
      $user_id = $currentUser->id;
      $result = $this->testUserAthenticity($user_id);

      if ($result['success'] === true) {
        // dd($currentUser);
        $wallet = $currentUser->wallet;
        if ($wallet) {
          $wallet_status = $this->checkDefaulterWallet($wallet);

          if ($wallet->status === 'active') {
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            $balance = $wallet->getBalance();

            $transaction = Transaction::create([
              'wallet_id' => $wallet->id,
              'payable_type' => 'App\User',
              'payable_id' => $user_id,
              'ref_id' => $ref_id,
              'type' => 'deposit',
              'holder_id' => $user_id,
              'amount' => $amount,
              'meta' => $meta,
              'confirmed' => $confirmed,
              'uuid' => Str::uuid(),
            ]);

            if ($ref_id) {
              $checkRefund = Transaction::where('ref_id', $ref_id)->where('type', 'deposit')->count();
              if ($checkRefund > 1) {
                DB::rollBack();
                throw new \Exception("Transaction Failed (Already Refunded)...");
              }
            }

            if ($transaction && $confirmed) {
              $wallet->balance += $amount;
              $wallet->trx_balance += $amount;
              $wallet->update();
            }

            $results = [
              'old_balance' => $balance ?? 0,
              'new_balance' => $wallet->balance ?? 0,
              'wallet_trx_bal' => $wallet->trx_balance ?? 0,
              'wallet_status' => $wallet->status ?? null,
              'trx_id' => $transaction->id ?? null,
              'issue' => $wallet->status ?? null,
              'transaction' => $transaction->toArray() ?? [],
            ];
          } else {
            //   return $wallet_status;
            if ($wallet_status['success'] === false) {
              $results = [
                'old_balance' => $balance ?? 0,
                'new_balance' => $wallet->balance ?? 0,
                'wallet_trx_bal' => $wallet->trx_balance ?? 0,
                'wallet_status' => $wallet->status ?? null,
                'trx_id' => $transaction->id ?? null,
                'issue' => $wallet->status['data'] ?? null,
                'transaction' => $transaction->toArray() ?? [],
              ];
            } else {
              $results = [
                'old_balance' => $balance ?? 0,
                'new_balance' => $wallet->balance ?? 0,
                'wallet_trx_bal' => $wallet->trx_balance ?? 0,
                'wallet_status' => $wallet->status ?? null,
                'trx_id' => $transaction->id ?? null,
                'issue' => $wallet->status ?? null,
                'transaction' => $transaction->toArray() ?? [],
              ];
            }

            DB::commit();
            return Helper::ajaxResponse($results, 302, "User: $currentUser->name, Wallet ID: $wallet->id, Deposite ($amount), Transaction Failed...");
          }


          DB::commit();
          $user->refresh();
          return Helper::ajaxResponse($results, 200, "User: $currentUser->name, Wallet ID: $wallet->id, Deposite ($amount), Transaction Successfull...");
        } else {
          $results = [
            'old_balance' => $balance ?? 0,
            'new_balance' => $wallet->balance ?? 0,
            'wallet_trx_bal' => $wallet->trx_balance ?? 0,
            'wallet_status' => $wallet->status ?? null,
            'trx_id' => $transaction->id ?? null,
            'issue' => $wallet->status ?? null,
            'transaction' => $transaction->toArray() ?? [],
          ];
          DB::commit();
          return Helper::ajaxResponse($results, 302, 'wallet is not exist...!');
        }

        // dd($balance, $amount);

        // return $transaction;

      } else {
        $results = [
          'old_balance' => $balance ?? 0,
          'new_balance' => $wallet->balance ?? 0,
          'wallet_trx_bal' => $wallet->trx_balance ?? 0,
          'wallet_status' => $wallet->status ?? null,
          'trx_id' => $transaction->id ?? null,
          'issue' => $wallet->status ?? null,
          'transaction' => $transaction->toArray() ?? [],
        ];
        DB::rollBack();
        return Helper::ajaxResponse($result, 302, 'wallet is Banned or not exist...!');
      }
    } catch (Exception $e) {
      $results = [
        'old_balance' => $balance ?? 0,
        'new_balance' => $wallet->balance ?? 0,
        'wallet_trx_bal' => $wallet->trx_balance ?? 0,
        'wallet_status' => $wallet->status ?? null,
        'trx_id' => $transaction->id ?? null,
        'issue' => $e->getTrace() ?? null,
        'transaction' => $transaction->toArray() ?? [],
      ];
      DB::rollBack();
      return Helper::ajaxResponse($results, 302, "Exception(WM-69): " . $e->getMessage());
    }
  }

  public function depositForce($user = null, int|float $amount, ?array $meta = null, $ref_id = null, bool $confirmed = true)
  {
    try {
      DB::beginTransaction();
      $currentUser = $user ?? auth()->user();
      //   $currentUser->refresh();
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
            'ref_id' => $ref_id,
            'type' => 'deposit',
            'holder_id' => $user_id,
            'amount' => $amount,
            'meta' => $meta,
            'confirmed' => $confirmed,
            'uuid' => Str::uuid(),
          ]);

          $checkRefund = Transaction::where('ref_id', $ref_id)->where('type', 'deposit')->count();
          if ($checkRefund > 1) {
            DB::rollBack();
            throw new \Exception("Transaction Failed (Already Refunded)...");
          }

          if ($transaction && $confirmed) {
            $wallet->balance += $amount;
            $wallet->trx_balance += $amount;
            $wallet->update();
          }

          $results = [
            'old_balance' => $balance ?? 0,
            'new_balance' => $wallet->balance ?? 0,
            'wallet_trx_bal' => $wallet->trx_balance ?? 0,
            'wallet_status' => $wallet->status ?? null,
            'trx_id' => $transaction->id ?? null,
            'issue' => $wallet->status ?? null,
            'transaction' => $transaction->toArray() ?? [],
          ];
          DB::commit();
          $user->refresh();
          return Helper::ajaxResponse($results, 200, "User: $currentUser->name, Wallet ID: $wallet->id, Deposite ($amount), Transaction Successfull...");
        } else {
          $results = [
            'old_balance' => $balance ?? 0,
            'new_balance' => $wallet->balance ?? 0,
            'wallet_trx_bal' => $wallet->trx_balance ?? 0,
            'wallet_status' => $wallet->status ?? null,
            'trx_id' => $transaction->id ?? null,
            'issue' => $wallet->status ?? null,
            'transaction' => transaction->toArray() ?? [],
          ];
          DB::commit();
          return Helper::ajaxResponse($result, 302, 'wallet is not exist...!');
        }
      } else {
        DB::rollBack();
        $results = [
          'old_balance' => $balance ?? 0,
          'new_balance' => $wallet->balance ?? 0,
          'wallet_trx_bal' => $wallet->trx_balance ?? 0,
          'wallet_status' => $wallet->status ?? null,
          'trx_id' => $transaction->id ?? null,
          'issue' => $wallet->status ?? null,
          'transaction' => $transaction->toArray() ?? [],
        ];
        return Helper::ajaxResponse($result, 302, 'wallet is Banned or not exist...!');
      }
    } catch (Exception $e) {
      DB::rollBack();
      return Helper::ajaxResponse($e->getTrace(), 302, "Exception(WM-69): " . $e->getMessage());
    }
  }

  public function withdraw($user = null, int|float $amount, ?array $meta = null, $ref_id = null, bool $confirmed = true)
  {
    try {
      DB::beginTransaction();
      $currentUser = $user ?? User::find(auth()->user())->with('wallet')->first();

      $user_id = $currentUser->id;
      $result = $this->testUserAthenticity($user_id);

      if ($result['success'] === true) {
        $wallet = $currentUser->wallet;

        if ($wallet) {
          $wallet = Wallet::where('id', $wallet->id)->first();
          $wallet_status = $this->checkDefaulterWallet($wallet);
          if ($wallet->status === 'active') {
            $balance = $wallet->getBalance();

            if ($balance < 0) {
              $wallet->status = 'Banned';
              $wallet->update();

              DB::commit();
              return Helper::ajaxResponse($wallet, 302, "User Banned due to Negative Balance...");
            }
            if ($balance < $amount || $balance == 0) {
              $reqBalance = $amount - $balance;

              DB::rollBack();
              return Helper::ajaxResponse($wallet->toArray(), 302, "Low Balance (current: $balance, required more: $reqBalance)...!");
            }

            //   $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            $transaction = Transaction::create([
              'wallet_id' => $wallet->id,
              'payable_type' => 'App\User',
              'payable_id' => $user_id,
              'ref_id' => $ref_id,
              'type' => 'withdraw',
              'holder_id' => $user_id,
              'amount' => -$amount,
              'meta' => $meta,
              'confirmed' => $confirmed,
              'uuid' => Str::uuid(),
            ]);

            if ($ref_id != null) {
              $checkRefund = Transaction::where('ref_id', $ref_id)->where('type', 'withdraw')->count();
              if ($checkRefund > 1) {
                DB::rollBack();
                throw new \Exception("Transaction Failed (Already Withdraw)...");
              }
            }

            if ($transaction && $confirmed) {
              $balance = $wallet->getBalance();
              $trx_bal = $wallet->trx_balance;
              $wallet->balance = $balance - $amount;
              $wallet->trx_balance = $trx_bal - $amount;
              $wallet->update();
            }

            DB::commit();

            $results = [
              'old_balance' => $balance ?? 0,
              'new_balance' => $wallet->balance,
              'wallet_trx_bal' => $wallet->trx_balance,
              'wallet_status' => $wallet->status ?? null,
              'trx_id' => $transaction->id ?? null,
              'issue' => $wallet->status ?? null,
              'transaction' => $transaction->toArray() ?? [],
            ];
            $user->refresh();
            $wallet->refresh();
            return Helper::ajaxResponse($results, 200, "User: $currentUser->name, Wallet ID: $wallet->id, Withdrawal ($amount), Transaction Successfull...");
          } else {
            //   return $wallet_status;
            if ($wallet_status['success'] === false) {
              $results = [
                'old_balance' => $balance ?? 0,
                'new_balance' => $wallet->balance,
                'wallet_trx_bal' => $wallet->trx_balance,
                'wallet_status' => $wallet->status ?? null,
                'trx_id' => $transaction->id ?? null,
                'issue' => $wallet->status ?? null,
                'transaction' => $transaction->toArray() ?? [],
              ];
            } else {
              $results = [
                'old_balance' => $balance ?? 0,
                'new_balance' => $wallet->balance ?? 0,
                'wallet_trx_bal' => $wallet->trx_balance ?? 0,
                'wallet_status' =>  $wallet->status ?? null,
                'trx_id' => $transaction->id ?? null,
                'issue' => $wallet->status ?? null,
                'transaction' => $transaction->toArray() ?? [],
              ];
            }
            DB::commit();
            return Helper::ajaxResponse($results, 302, "User: $currentUser->name, Wallet ID: $wallet->id, Withdrawal ($amount), Transaction Failed due to trx balance mismatch...");
          }
        }
        // return $transaction;
        else {
          DB::commit();
          $user = null;
          $results = [
            'old_balance' => 0,
            'new_balance' => 0,
            'wallet_trx_bal' => 0,
            'wallet_status' => 'not found',
            'transaction' => [],
          ];
          return Helper::ajaxResponse($results, 302, 'wallet is not exist...!');
        }
      } else {
        DB::rollBack();
        $results = [
          'old_balance' => 0,
          'new_balance' => 0,
          'wallet_trx_bal' => 0,
          'wallet_status' => 'wallet is Banned',
          'transaction' => [],
        ];
        return Helper::ajaxResponse($result, 302, 'wallet is Banned or not exist...!');
      }
    } catch (Exception $e) {
      DB::rollBack();
      $results = [
        'old_balance' => 0,
        'new_balance' => 0,
        'wallet_trx_bal' => 0,
        'wallet_status' => 'wallet is Banned',
        'issue' => $e->getTrace(),
        'transaction' => [],
      ];
      return Helper::ajaxResponse($results, 302, "Exception(WM-126): " . $e->getMessage());
    }
  }

  public function withdrawForce($user = null, int|float $amount, ?array $meta = null, $ref_id = null, bool $confirmed = true)
  {
    try {
      DB::beginTransaction();
      $currentUser = $user ?? User::find(auth()->user())->with('wallet')->first();

      $user_id = $currentUser->id;
      $result = $this->testUserAthenticity($user_id);

      if ($result['success'] === true) {
        $wallet = $currentUser->wallet;

        if ($wallet) {
          $wallet = Wallet::where('id', $wallet->id)->first();
          $wallet_status = $this->checkDefaulterWallet($wallet);
          if ($wallet->status === 'active') {
            $balance = $wallet->getBalance();

            if ($balance < 0) {
              $wallet->status = 'Banned';
              $wallet->description = "User Banned due to Negative Balance...";
              $wallet->update();

              DB::commit();
              return Helper::ajaxResponse($wallet->toArray(), 302, "User Banned due to Negative Balance...");
            }

            $transaction = Transaction::create([
              'wallet_id' => $wallet->id,
              'payable_type' => 'App\User',
              'payable_id' => $user_id,
              'ref_id' => $ref_id,
              'type' => 'withdraw',
              'holder_id' => $user_id,
              'amount' => -$amount,
              'meta' => $meta,
              'confirmed' => $confirmed,
              'uuid' => Str::uuid(),
            ]);

            if ($transaction && $confirmed) {
              $balance = $wallet->getBalance();
              $trx_bal = $wallet->trx_balance;
              $wallet->balance = $balance - $amount;
              $wallet->trx_balance = $trx_bal - $amount;
              $wallet->update();
            }

            DB::commit();

            $results = [
              'old_balance' => $balance ?? 0,
              'new_balance' => $wallet->balance ?? 0,
              'wallet_trx_bal' => $wallet->trx_balance ?? null,
              'wallet_status' => $wallet->status ?? null,
              'trx_id' => $transaction->id ?? null,
              'issue' => null,
              'transaction' => $transaction->toArray() ?? [],
            ];
            $user->refresh();
            $wallet->refresh();
            return Helper::ajaxResponse($results, 200, "User: $currentUser->name, Wallet ID: $wallet->id, Withdrawal ($amount), Transaction Successfull...");
          } else {
            //   return $wallet_status;
            if ($wallet_status['success'] === false) {
              $ws = $wallet_status['data'];
            } else {
              $ws = $wallet_status;
            }
            $results = [
              'old_balance' => $balance ?? 0,
              'trx_id' => $transaction->id ?? null,
              'new_balance' => $wallet->balance ?? 0,
              'wallet_trx_bal' => $wallet->trx_balance ?? 0,
              'wallet_status' => $wallet->status ?? null,
              'issue' => $ws ?? null,
              'transaction' => $transaction->toArray() ?? null,
            ];
            DB::commit();
            return Helper::ajaxResponse($results, 302, "User: $currentUser->name, Wallet ID: $wallet->id, Withdrawal ($amount), Transaction Failed due to trx balance mismatch...");
          }
        }
        // return $transaction;
        else {
          DB::commit();

          $results = [
            'old_balance' => $balance ?? 0,
            'trx_id' => $transaction->id ?? null,
            'new_balance' => $wallet->balance ?? 0,
            'wallet_trx_bal' => $wallet->trx_balance ?? 0,
            'wallet_status' => $wallet->status ?? null,
            'issue' => $result ?? null,
            'transaction' => $transaction->toArray() ?? null,
          ];

          $user = null;
          return Helper::ajaxResponse($results, 302, 'wallet is not exist...!');
        }
      } else {
        DB::rollBack();
        $results = [
          'old_balance' => $balance ?? 0,
          'trx_id' => $transaction->id ?? null,
          'new_balance' => $wallet->balance ?? 0,
          'wallet_trx_bal' => $wallet->trx_balance ?? 0,
          'wallet_status' => $wallet->status ?? null,
          'issue' => $result ?? null,
          'transaction' => $transaction->toArray() ?? null,
        ];
        return Helper::ajaxResponse($results, 302, 'wallet is Banned or not exist...!');
      }
    } catch (Exception $e) {
      DB::rollBack();
      $results = [
        'old_balance' => $balance ?? 0,
        'trx_id' => $transaction->id ?? null,
        'new_balance' => $wallet->balance ?? 0,
        'wallet_trx_bal' => $wallet->trx_balance ?? 0,
        'wallet_status' => $wallet->status ?? null,
        'issue' => $result ?? null,
        'transaction' => $transaction->toArray() ?? null,
      ];
      return Helper::ajaxResponse($results, 302, "Exception(WM-126): " . $e->getMessage());
    }
  }

  public function confirm($trx)
  {
    try {
      // $trx = Transaction::find($id);
      if ($trx)
        if ($trx->confirmed != true || $trx->confirmed != 1) {

          $trx->confirmed = true;
          $trx->update();

          $wallet = Wallet::find($trx->wallet_id);
          $wallet->balance += $trx->amount;
          $wallet->trx_balance += $trx->amount;
          // $wallet->meta += ['approved_by' => auth()->user()->id];
          $wallet->update();
          return Helper::ajaxResponse([], 200, 'Transaction Approved Successfully');
        } else {
          return Helper::ajaxResponse([], 302, 'Transaction Approved Successfully');
        }
    } catch (\Exception $e) {
      return Helper::ajaxResponse($e->getMessage(), 302, 'Transaction Approved Successfully');
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

  protected function compareTransactions(Wallet $wallet)
  {
    $error = false;
    $userName = null;
    // $wallet = Wallet::find($id);
    $user = $wallet ? User::find($wallet->holder_id) : $error = true;
    if ($user) {
      $userName = $user->name;
    }
    if ($error === true) {
      return Helper::ajaxResponse($error, 302, "Wallet not Found");
    }
    $deposit = Transaction::where('wallet_id', $wallet->id)->where('payable_id', $wallet->holder_id)->where('type', 'deposit')->where('confirmed', 1)->sum('amount');
    $withdraw = Transaction::where('wallet_id', $wallet->id)->where('payable_id', $wallet->holder_id)->where('type', 'withdraw')->where('confirmed', 1)->sum('amount');

    $balance = $deposit - abs($withdraw);

    if ($wallet->getBalance() === (int) $balance) {
      // dd($wallet->getBalance(), $balance);
      $wallet->trx_balance = $balance;
      $wallet->update();
      return Helper::ajaxResponse(["Wallet Ok"], 200, "Wallet Ok");
    } else {
      $wallet->status = 'defaulter';
      $wallet->description = 'Transaction Mismatch';
      $wallet->trx_balance = $balance;
      $wallet->update();

      $results = [
        'wallet' => $wallet->toArray(),
        'transaction_balance' => $balance
      ];
      return Helper::ajaxResponse($results, 302, "Wallet Issue Detected");
    }
  }

  protected function checkDefaulterWallet($wallet)
  {
    if ($wallet) {
      $faulter = $this->compareTransactions($wallet);
      if ($faulter['success'] === false && $faulter['data'] != 1) {
        $data[] = [
          'holder_id' => $faulter['data']['wallet']['holder_id'],
          'id' => $faulter['data']['wallet']['id'],
          'wallet_balance' => $faulter['data']['wallet']['balance'],
          'transactions_balance' => $faulter['data']['transaction_balance'],
          'status' => $faulter['data']['wallet']['status'],
        ];
        // dd($data);
        $wallet->first_run = 1;
        $wallet->update();
        return Helper::ajaxResponse($data, 302, "Wallet Issue Detected");
      } else {
        $wallet->description = 'Wallet is working...';
        $wallet->status = "active";
        $wallet->update();
        return Helper::ajaxResponse($faulter, 200, "Wallet OK");
      }
    }
    return Helper::ajaxResponse($wallet, 302, "Wallet is not found");
  }

  public function balance()
  {
    $wallet = Wallet::where('holder_id', $this->id)->first();
    if ($wallet) {
      return $wallet->balance;
    } else {
      return 0;
    }
  }
  public function wallet()
  {
    return $this->hasOne(Wallet::class, 'holder_id', 'id');
  }

  public function transactions($id) {
    return Transaction::find($id);
  }
}
