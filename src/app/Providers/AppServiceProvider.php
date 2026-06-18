<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ログインしていないユーザーが弾かれた時の移動先を/loginに固定
        Authenticate::redirectUsing(function ($request) {
            return route('login');
        });

        // メール認証していないユーザーがログインしようとした時の移動先をメール認証誘導画面に固定
        EnsureEmailIsVerified::redirectTo('email/verify');
    }
}
