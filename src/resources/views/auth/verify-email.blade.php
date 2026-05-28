@extends('layouts.app')

@section('title', 'メール認証誘導画面')

@section('css')
    <!-- 💡 ご提示いただいたCSSファイルを読み込みます -->
    @vite(['resources/css/verify-email.css'])
@endsection

@section('content')
    <div class="container">
        <div class="verify-box">

            <!-- 💡 メッセージ（見本通りの文言に調整しています） -->
            <div class="message">
                登録していただいたメールアドレスに認証メールを送付しました。<br>
                メール認証を完了してください。
            </div>

            <!-- 💡 仕様3・4-c: 「認証はこちらから」ボタン -->
            <!-- ボタンを押すと、裏で日時を指定（パス）して勤怠画面（d）へ直行します -->
            <form method="POST" action="{{ route('email.bypass') }}">
                @csrf
                <button type="submit" class="btn-verify">
                    認証はこちらから
                </button>
            </form>

            <!-- 💡 見本の下部にある「認証メールを再送する」リンク（Laravel標準機能） -->
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="resend-link">
                    認証メールを再送する
                </button>
            </form>

        </div>
    </div>
@endsection
