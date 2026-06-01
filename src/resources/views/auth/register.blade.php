@extends('layouts.app') <!-- 👈 これで共通の黒ヘッダーと app.css が自動で読み込まれます -->

@section('title', '会員登録（一般ユーザー）')

@section('css')
    <!-- 💡 app.css は親が読み込むので、ここでは会員登録専用のCSSだけを指定します -->
    @vite(['resources/css/register.css'])
@endsection

@section('content')
    <!-- 💡 もともとの <body> 内にあった中身をここにすべて収めます -->
    <div class="register-box">
        <h1>会員登録</h1>

        <form action="/register" method="POST">
            @csrf

            <!-- お名前入力欄 -->
            <div class="form-group">
                <label>お名前</label>
                <input type="text" name="name" value="{{ old('name') }}">
                @error('name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- メールアドレス入力欄 -->
            <div class="form-group">
                <label>メールアドレス</label>
                <input type="text" name="email" value="{{ old('email') }}">
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- パスワード入力欄 -->
            <div class="form-group">
                <label>パスワード</label>
                <input type="password" name="password">
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- 確認用パスワード入力欄 -->
            <div class="form-group">
                <label>確認用パスワード</label>
                <input type="password" name="password_confirmation">
            </div>

            <!-- 黒ボタン用のクラスを追加 -->
            <button type="submit" class="btn">登録</button>
        </form>

        <p><a href="/login" class="login-link">ログインはこちら</a></p>
    </div>
@endsection
