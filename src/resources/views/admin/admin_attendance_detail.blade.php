@extends('layouts.admin')

@section('title', '勤怠詳細')

@section('css')
@vite(['resources/css/admin_attendance_detail.css'])
@endsection

@section('content')
<div class="attendanceDetailMain">
    <div class="attendanceDetailForm">
        <h1 class="attendanceDetailTitle">勤怠詳細</h1>

        <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
            <!-- 不正なフォーム送信を防ぐための認証トークン-->
            @csrf
            <!-- HTTPメソッドを更新処理に変更 -->
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
                                <!-- 出勤時刻入力欄 -->
                                <input class="inputTimeField" type="time" name="clock_in"
                                    value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}" {{ $isPending ? 'readonly' : '' }}>
                                <span class="timeSeparator">〜</span>
                                <!-- 退勤時刻入力欄 -->
                                <input class="inputTimeField" type="time" name="clock_out"
                                    value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}" {{ $isPending ? 'readonly' : '' }}>
                            </div>
                            @if ($errors->has('clock_in') || $errors->has('clock_out'))
                                <p class="inputErrorMessage">
                                    {{ $errors->first('clock_in') ?: $errors->first('clock_out') }}
                                </p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>休憩</th>
                        <td>
                            <div class="timeRangeGroup">
                                <!-- 休憩データのIDを更新用に送信 -->
                                <input type="hidden" name="breaks[0][id]" value="{{ $attendance->breaks[0]->id ?? '' }}">
                                <!-- 休憩開始時間の入力欄 -->
                                <input class="inputTimeField" type="time" name="breaks[0][break_in]"
                                    value="{{ old('breaks.0.break_in', isset($attendance->breaks[0]) && $attendance->breaks[0]->break_in ? \Carbon\Carbon::parse($attendance->breaks[0]->break_in)->format('H:i') : '') }}" {{ $isPending ? 'readonly' : '' }}>
                                    <!-- 区切り表示 -->
                                    <span class="timeSeparator">〜</span>
                                    <!-- 休憩終了入力欄 -->
                                    <input class="inputTimeField" type="time" name="breaks[0][break_out]"
                                    value="{{ old('breaks.0.break_out', isset($attendance->breaks[0]) && $attendance->breaks[0]->break_out ? \Carbon\Carbon::parse($attendance->breaks[0]->break_out)->format('H:i') : '') }}" {{ $isPending ? 'readonly' : '' }}>
                            </div>

                            @error('breaks.0.break_in')
                                <span class="inputErrorMessage">{{ $message }}</span>
                            @enderror

                            @error('breaks.0.break_out')
                                <span class="inputErrorMessage">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <th>休憩2</th>
                        <td>
                            <div class="timeRangeGroup">
                                <input type="hidden" name="breaks[1][id]" value="{{ $attendance->breaks[1]->id ?? '' }}">
                                <input class="inputTimeField" type="time" name="breaks[1][break_in]"
                                    value="{{ old('breaks.1.break_in', isset($attendance->breaks[1]) && $attendance->breaks[1]->break_in ? \Carbon\Carbon::parse($attendance->breaks[1]->break_in)->format('H:i') : '') }}" {{ $isPending ? 'readonly' : '' }}>
                                <span class="timeSeparator">〜</span>
                                <input class="inputTimeField" type="time" name="breaks[1][break_out]"
                                    value="{{ old('breaks.1.break_out', isset($attendance->breaks[1]) && $attendance->breaks[1]->break_out ? \Carbon\Carbon::parse($attendance->breaks[1]->break_out)->format('H:i') : '') }}" {{ $isPending ? 'readonly' : '' }}>
                            </div>

                            @error('breaks.1.break_in')
                                <span class="inputErrorMessage">{{ $message }}</span>
                            @enderror

                            @error('breaks.1.break_out')
                                <span class="inputErrorMessage">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>

                    <tr>
                        <th>備考</th>
                        <td>
                            <textarea class="textareaRemarksField" name="comment" {{ $isPending ? 'readonly' : '' }}>{{ old('comment', $attendance->comment) }}</textarea>

                            @error('comment')
                                <span class="inputErrorMessage">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                </tbody>
            </table>

            @if (!$isPending)
                <div class="formActionsPanel">
                    <button class="submitUpdateButton" type="submit">修正</button>
                </div>
            @endif
        </form>
    </div>

    @if ($isPending)
        <div class="approvalPendingOutside">
            <p class="approvalPendingMessage">＊承認待ちのため修正はできません。</p>
        </div>
    @endif
</div>
@endsection
