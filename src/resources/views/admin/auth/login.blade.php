@extends('layouts.admin')

@section('title', '管理者ログイン')

@section('css')
@vite(['resources/css/admin_login.css'])
@endsection

@section('content')
<div class="login-container">
    <h1 class="login-title">管理者ログイン</h1>

    <!-- ログイン情報が登録されていない場合のエラー表示 -->
     @if (session('login_failed') || $errors->has('login_failed'))
            <div class="error-message">
                {{ session('login_failed') ?? $errors->first('login_failed') }}
            </div>
        @endif

    <!-- 管理者ログイン情報を送信するフォーム（HTML標準バリデーションは無効） -->
    <form action="{{ route('admin.login.submit') }}" method="POST" novalidate>
        <!-- cookie自動送信を悪用した攻撃を防ぐための@csrf(セキュリティトークン) -->
        @csrf

        <div class="form-group">
            <label class="form-label" for="email">メールアドレス</label>
            <!-- メールアドレス入力欄 -->
            <input class="form-input" type="email" id="email" name="email" value="{{ old('email') }}">
            @error('email')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password">パスワード</label>
            <!-- パスワード入力欄 -->
            <input class="form-input" type="password" id="password" name="password">
            @error('password')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <button class="form-button" type="submit">管理者ログインする</button>
    </form>
</div>
@endsection
