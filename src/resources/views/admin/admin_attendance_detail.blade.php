@extends('layouts.admin')

@section('title', '勤怠詳細')

@section('css')
@vite(['resources/css/admin_attendance_detail.css'])
@endsection

@section('header_menu')
<nav class="header__nav">
    <ul class="header__nav-list">
        <li class="header__nav-item">
            <a class="header__nav-link" href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
        </li>
        <li class="header__nav-item">
            <a class="header__nav-link" href="#">スタッフ一覧</a>
        </li>
        <li class="header__nav-item">
            <a class="header__nav-link" href="#">申請一覧</a>
        </li>
        <li class="header__nav-item">
            <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;">
                @csrf
                <button class="header__nav-link" type="submit" style="background: none; border: none; font-family: inherit;">ログアウト</button>
            </form>
        </li>
    </ul>
</nav>
@endsection

@section('content')
<div class="attendanceDetailMain">
    <div class="attendanceDetailForm">
        <h1 class="attendanceDetailTitle">勤怠詳細</h1>

        <form action="#" method="POST">
            @csrf
            @method('PATCH')

            <table class="attendanceTable">
                <tbody>
                    <tr>
                        <th>名前</th>
                        <td>{{ $attendance->user->name }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>
                            <div class="dateDisplayGroup">
                                <span class="dateYearText">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                                <span class="dateDayText">{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <div class="timeRangeGroup">
                                <input class="inputTimeField" type="time" name="clock_in" value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}">
                                <span class="timeSeparator">〜</span>
                                <input class="inputTimeField" type="time" name="clock_out" value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">
                            </div>
                            @error('clock_in')
                                <span class="inputErrorMessage">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <th>休憩</th>
                        <td>
                            <div class="timeRangeGroup">
                                <input class="inputTimeField" type="time" name="break_in" value="{{ $attendance->breakLogs->isNotEmpty() && isset($attendance->breakLogs[0]) ? \Carbon\Carbon::parse($attendance->breakLogs[0]->break_in)->format('H:i') : '' }}">
                                <span class="timeSeparator">〜</span>
                                <input class="inputTimeField" type="time" name="break_out" value="{{ $attendance->breakLogs->isNotEmpty() && isset($attendance->breakLogs[0]) ? \Carbon\Carbon::parse($attendance->breakLogs[0]->break_out)->format('H:i') : '' }}">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>休憩2</th>
                        <td>
                            <div class="timeRangeGroup">
                                <input class="inputTimeField" type="time" name="break2_in" value="{{ $attendance->breakLogs->isNotEmpty() && isset($attendance->breakLogs[1]) ? \Carbon\Carbon::parse($attendance->breakLogs[1]->break_in)->format('H:i') : '' }}">
                                <span class="timeSeparator">〜</span>
                                <input class="inputTimeField" type="time" name="break2_out" value="{{ $attendance->breakLogs->isNotEmpty() && isset($attendance->breakLogs[1]) ? \Carbon\Carbon::parse($attendance->breakLogs[1]->break_out)->format('H:i') : '' }}">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>備考</th>
                        <td>
                            <textarea class="textareaRemarksField" name="remarks">{{ $attendance->remarks }}</textarea>
                            @error('remarks')
                                <span class="inputErrorMessage">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="formActionsPanel">
                <button class="submitUpdateButton" type="submit">修正</button>
            </div>
        </form>
    </div>
</div>
@endsection
