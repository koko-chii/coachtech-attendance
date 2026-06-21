@extends('layouts.admin')

@section('title', '勤怠一覧')

@section('css')
@vite(['resources/css/admin_attendance_list.css'])
@endsection

@section('content')
<div class="attendance-list-container">
    <!-- 何月日の表示 -->
    <h1>{{ $date->format('Y年n月j日') }}の勤怠</h1>

     <!-- 修正完了メッセージ -->
    @if (session('success_message'))
        <div class="success-message-wrapper">
            <p class="successMessage">{{ session('success_message') }}</p>
        </div>
    @endif

    <!-- 日付変更エリア -->
    <nav class="date-selector">
        <a class="nav-btn" href="{{ route('admin.attendance.list', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}">← 前日</a>
        <span class="current-date-display">📅 {{ $date->format('Y/m/d') }}</span>
        <a class="nav-btn" href="{{ route('admin.attendance.list', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}">翌日 →</a>
    </nav>

    <!-- テーブルエリア -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendanceRecords as $record)
                    <tr>
                        <!-- ユーザー名を表示 -->
                        <td>{{ $record->user->name }}</td>
                        <!-- 出勤時刻をH:i形式で表示 -->
                        <td>{{ $record->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('H:i') : '' }}</td>
                        <!-- 退勤時刻をH:i形式で表示 -->
                        <td>{{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('H:i') : '' }}</td>
                        <!-- 休憩時間を計算して表示 -->
                        <td>
                            @if($record->breakLogs->isNotEmpty() && $record->breakLogs->first()->break_in && $record->breakLogs->first()->break_out)
                                {{ \Carbon\Carbon::parse($record->breakLogs->first()->break_in)->diff(\Carbon\Carbon::parse($record->breakLogs->first()->break_out))->format('%H:%I') }}
                            @endif
                        </td>
                        <!-- 勤務時間を計算して表示 -->
                        <td>
                            @if($record->clock_in && $record->clock_out)
                                {{ \Carbon\Carbon::parse($record->clock_in)->diff(\Carbon\Carbon::parse($record->clock_out))->format('%H:%I') }}
                            @endif
                        </td>
                        <td>
                            <a class="detail-link" href="{{ route('admin.attendance.detail', ['id' => $record->id]) }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
