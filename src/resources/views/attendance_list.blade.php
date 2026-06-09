@extends('layouts.app')

@section('title', '勤怠一覧画面')

@section('css')
    @vite(['resources/css/attendance_list.css'])
@endsection

@section('content')
<!-- 勤怠一覧画面全体のレイアウトを整えるコンテナ -->
<div id="attendance-list-container">
    
    <h1>勤怠一覧</h1>

    <!-- 指定月とその前月・翌月の選択欄 -->
    <div id="month-selector">
        <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="nav-btn">&larr; 前月</a>
        <span class="current-month-display">
            <span>📅</span> {{ $currentMonth }}
        </span>
        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="nav-btn">翌月 &rarr;</a>
    </div>

    <!-- 勤怠データーを表示する全体の囲み -->
    <div class="table-wrapper">
        <table>
            <!--表の項目名  -->
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                <!-- コントローラーで用意した１日から月末を1日ずつ持ってきてループ処理をする -->
                <!-- データーを年月日形式にして変数(箱)にしまう -->
                <!--1日ずつ打刻データがあるか探し変数(箱)にしまう  -->
                <!-- 日本語の曜日を用意して変数(箱)にしまう -->
                <!-- 日付に合った正しい曜日を用意して変数(箱)にしまう -->
                @foreach($daysInMonth as $day)
                    @php
                        $dateStr = $day->format('Y-m-d');
                        $record = $attendances->get($dateStr);
                        $wago = ['日', '月', '火', '水', '木', '金', '土'];
                        $dayOfWeek = $wago[$day->dayOfWeek];

                        //休憩合計秒数を計算する空の箱を毎回用意する
                        $totalBreakSeconds = 0;

                        //　もし休憩を複数していたら
                        if ($record) {
                            $breaks = \Illuminate\Support\Facades\DB::table('breaks')
                                ->where('attendance_record_id', $record->id)
                                ->get();

                            foreach ($breaks as $break) {
                                if ($break->break_in && $break->break_out) {
                                    $in = \Carbon\Carbon::parse($break->break_in);
                                    $out = \Carbon\Carbon::parse($break->break_out);
                                    $totalBreakSeconds += $in->diffInSeconds($out);
                                }
                            }
                        }

                        // 合計休憩時間を秒計算し、何時間何分に変換し変数(箱)にしまう
                        $breakHours = floor($totalBreakSeconds / 3600);
                        $breakMinutes = floor(($totalBreakSeconds % 3600) / 60);
                        $breakTimeStr = $totalBreakSeconds > 0 ? sprintf('%02d:%02d', $breakHours, $breakMinutes) : '00:00';

                        // もし出勤記録があれば(退勤時間-出勤時間-休憩時間）勤務秒数を計算
                        $workTimeStr = '';
                        if ($record && $record->clock_in && $record->clock_out) {
                            $start = \Carbon\Carbon::parse($record->clock_in);
                            $end = \Carbon\Carbon::parse($record->clock_out);
                            $totalWorkSeconds = $start->diffInSeconds($end) - $totalBreakSeconds;
                            
                            // もし退勤ボタン押し忘れてマイナスの場合0として扱う
                            // 退勤時刻00:00－出金時刻09:00－休憩時刻01:00 ＝ マイナス 10時間
                            if ($totalWorkSeconds < 0) { $totalWorkSeconds = 0; }

                            // 合計勤務秒数を何時間何分に変換し変数(箱)にしまう
                            $workHours = floor($totalWorkSeconds / 3600);
                            $workMinutes = floor(($totalWorkSeconds % 3600) / 60);
                            $workTimeStr = sprintf('%02d:%02d', $workHours, $workMinutes);
                        }
                    @endphp
                    <tr>
                        <!-- 月/日と(曜日)、出勤時刻、退勤時刻、休憩時間、勤務合計時間 を表示 -->
                        <td>{{ $day->format('m/d') }}({{ $dayOfWeek }})</td>
                        <td>{{ $record && $record->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('H:i') : '' }}</td>
                        <td>{{ $record && $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('H:i') : '' }}</td>
                        <td>{{ $record ? $breakTimeStr : '' }}</td>
                        <td>{{ $workTimeStr }}</td>
                        <td>
                            <!-- もし詳細リンクをクリックしたら、勤怠詳細画面を表示する -->
                            @if($record)
                                <a href="{{ route('attendance.detail', ['id' => $record->id]) }}" class="detail-link">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
