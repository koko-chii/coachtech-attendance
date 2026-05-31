<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH')</title>
    <!-- 共通のCSSをここで1回だけ読み込みます -->
    @vite(['resources/css/app.css'])
    <!-- 各画面ごとの専用CSSを読み込む場所を作っておきます -->
    @yield('css')
</head>
<body>
        <!-- 🖤 共通の黒ヘッダーバー -->
    <header class="header">
        <div class="header-inner">
            <!-- 💡 文字の「COACHTECH」を <img> タグ（画像）に書き換えます -->
            <div class="logo">
                <img src="{{ asset('img/logo.png') }}" alt="COACHTECH">
            </div>
            <nav class="nav">
                <a href="/attendance">勤怠</a>
                <a href="#">勤怠一覧</a>
                <a href="#">申請</a>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">ログアウト</a>
            </nav>
        </div>
    </header>

    <!-- ログアウト用の隠しフォーム -->
    <form id="logout-form" action="/logout" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- 💡 ここに各画面（出勤画面や一覧画面）の中身がパッと挟み込まれます -->
    <main>
        @yield('content')
    </main>

</body>
</html>
