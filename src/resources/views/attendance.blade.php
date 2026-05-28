@extends('layouts.app')

@section('title', '勤怠登録画面')

@section('css')
    @vite(['resources/css/attendance.css'])
@endsection

@section('content')
    <div class="container">
        <!-- 💡 出勤データがあるかないかで、上のタグの文字を切り替えます -->
        @if($attendance)
            <div class="status-tag">勤務中</div>
        @else
            <div class="status-tag">勤務外</div>
        @endif

        <div class="date">{{ \Carbon\Carbon::now()->format('Y年m月d日') }}(木)</div>

        <!-- 💡 現在のリアルな時刻を表示します（後ほど時計を動かします） -->
        <div class="time">{{ \Carbon\Carbon::now()->format('H:i') }}</div>

        <!-- 💡 データベースの状態でボタンを出し分けます -->
        @if(!$attendance)
            <!-- ⭕ まだ出勤していない場合のみ、出勤ボタンを表示 -->
            <form action="{{ route('attendance.clock-in') }}" method="POST">
                @csrf
                <button type="submit" class="btn">出勤</button>
            </form>
        @else
            <!-- ⭕ すでに出勤している場合は、見本の通り他のボタンに変えます（仮置き） -->
            <div style="display: flex; justify-content: center; gap: 20px;">
                <button class="btn" style="background-color: #aaa;" disabled>退勤</button>
                <button class="btn" style="background-color: #aaa;" disabled>休憩</button>
            </div>
        @endif
    </div>
@endsection
