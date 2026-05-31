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
            <a href="/email/go-to-mailpit" class="btn-verify" target="_blank">
                認証はこちらから
            </a>

            <!-- 💡 再送が成功したときにお知らせを表示する -->
            @if (session('status') == 'verification-link-sent')
                <div class="alert-success">
                    新しい認証メールを再送信しました！
                </div>
            @endif

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
