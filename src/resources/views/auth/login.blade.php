@extends('layouts.app') <!-- 👈 これで共通の黒ヘッダーと app.css が自動で読み込まれます -->

@section('title', 'ログイン（一般ユーザー）')

@section('css')
    <!-- 💡 app.css は親が読み込むので、ここではログイン専用のCSSだけを指定します -->
    @vite(['resources/css/login.css'])
@endsection

@section('content')
    <!-- 💡 もともとの <body> 内にあった中身をここにすべて収めます -->
    <div class="login-box">
        <h1>ログイン</h1>

        @if ($errors->any())
            <div class="error-message">
                @if ($errors->has('email') && str_contains($errors->first('email'), '登録されていません'))
                    {{ $errors->first('email') }}
                @endif
            </div>
        @endif

        <form action="/login" method="POST">
            @csrf

            <!-- メールアドレス入力欄 -->
            <div class="form-group">
                <label>メールアドレス</label>
                <input type="text" name="email" value="{{ old('email') }}">
                @error('email')
                    @if (!str_contains($message, '登録されていません'))
                        <div class="error-message">{{ $message }}</div>
                    @endif
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

            <!-- Figma風の黒ボタンクラス「btn」を追加 -->
            <button type="submit" class="btn">ログイン</button>
        </form>

        <p><a href="/register" class="login-link">会員登録はこちら</a></p>
    </div>
@endsection
