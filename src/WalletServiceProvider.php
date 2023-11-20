<?php

namespace Faanigee\Wallet;

use Illuminate\Support\ServiceProvider;

class WalletServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    $this->loadViewsFrom(__DIR__.'/resources/views', 'wallet');
    $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'wallet');

  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    //
  }

}
