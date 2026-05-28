<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 💡 ログインしていないユーザーが弾かれた時の移動先を「/login」に固定します
        Authenticate::redirectUsing(function ($request) {
            return route('login');
        });

        // 💡 メール認証していないユーザーがログインしようとした時の移動先を「メール認証誘導画面（/email/verify）」に固定します
        EnsureEmailIsVerified::redirectTo('email/verify');
    }
}
