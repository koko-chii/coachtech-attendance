@extends('layouts.app')

@section('title', '勤怠登録画面')

@section('css')
    @vite(['resources/css/attendance.css'])
@endsection

@section('content')
    <div class="container">

        <!-- ステータスタグ -->
        @if (!$attendance)
            <div class="status-tag">勤務外</div>
        @elseif ($attendance && !$attendance->clock_out)
            @if ($is_breaking)
                <div class="status-tag">休憩中</div>
            @else
                <div class="status-tag">出勤中</div>
            @endif
        @else
            <div class="status-tag">退勤済</div>
        @endif

        <!-- 日時情報 -->
        <div class="date">{{ $today }}</div>
        <div class="time">{{ \Carbon\Carbon::now()->format('H:i') }}</div>

        <!-- ボタンエリア -->
        <div class="button-area" style="display: flex; justify-content: center; gap: 30px;">

            @if (!$attendance)
                <!-- 出勤前：黒いボタン -->
                <form action="{{ route('attendance.clock-in') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-attendance btn-black">出勤</button>
                </form>

            @elseif ($attendance && !$attendance->clock_out)
                @if ($is_breaking)
                    <!-- 💡 休憩中：【見本画像2枚目】白背景の「休憩戻」ボタン -->
                    <form action="{{ route('attendance.break') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-attendance btn-white">休憩戻</button>
                    </form>
                @else
                    <!-- 💡 出勤後：【見本画像1枚目】黒い「退勤」と、白背景の「休憩入」ボタン -->
                    <form action="{{ route('attendance.clock-out') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-attendance btn-black">退勤</button>
                    </form>

                    <form action="{{ route('attendance.break') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-attendance btn-white">休憩入</button>
                    </form>
                @endif

            @else
                <!-- 退勤後：メッセージ表示 -->
                <p class="attendance-end-message">お疲れ様でした。</p>
            @endif

        </div>
    </div>
@endsection
