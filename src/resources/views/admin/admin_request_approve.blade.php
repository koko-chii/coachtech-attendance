@extends('layouts.admin')

@section('title', '勤怠詳細')

@section('css')
@vite(['resources/css/admin_request_approve.css'])
@endsection

@section('content')
<div class="attendanceDetailMain">
    <div class="attendanceDetailForm">
        <h1 class="attendanceDetailTitle">勤怠詳細</h1>

        <form action="{{ route('admin.request.approve.submit', $requestData->id) }}" method="POST">
            @csrf
            @method('PATCH')

            <table class="attendanceTable">
                <tbody>
                    <tr>
                        <th>名前</th>
                        <td>{{ $requestData->user->name }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>
                            <div class="dateDisplayGroup">
                                <span class="dateYearText">{{ \Carbon\Carbon::parse($requestData->attendanceRecord->date)->format('Y年') }}</span>
                                <span class="dateDayText">{{ \Carbon\Carbon::parse($requestData->attendanceRecord->date)->format('n月j日') }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <div class="timeRangeGroup">
                                <input class="inputTimeField" type="time" name="clock_in" value="{{ $requestData->requested_clock_in ? \Carbon\Carbon::parse($requestData->requested_clock_in)->format('H:i') : '' }}" readonly>
                                <span class="timeSeparator">〜</span>
                                <input class="inputTimeField" type="time" name="clock_out" value="{{ $requestData->requested_clock_out ? \Carbon\Carbon::parse($requestData->requested_clock_out)->format('H:i') : '' }}" readonly>
                            </div>
                        </td>
                    </tr>

                    @if(!empty($requestData->requested_breaks))
                        @foreach(array_values($requestData->requested_breaks) as $index => $break)
                            <tr>
                                <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                                <td>
                                    <div class="timeRangeGroup">
                                        <input class="inputTimeField" type="time" value="{{ isset($break['break_in']) ? \Carbon\Carbon::parse($break['break_in'])->format('H:i') : '' }}" readonly>
                                        <span class="timeSeparator">〜</span>
                                        <input class="inputTimeField" type="time" value="{{ isset($break['break_out']) ? \Carbon\Carbon::parse($break['break_out'])->format('H:i') : '' }}" readonly>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    <tr>
                        <th>備考</th>
                        <td>
                            <textarea class="textareaRemarksField" name="remarks" readonly>{{ $requestData->requested_remarks }}</textarea>
                        </td>
                    </tr>
                </tbody>
            </table>

            @if ($isPending)
                <div class="formActionsPanel">
                    <button class="submitUpdateButton" type="submit" name="action" value="approve">承認</button>
                </div>
            @endif
        </form>
    </div>

    @if (!$isPending)
        <div class="approvalPendingOutside">
            <p class="approvalPendingMessage">＊承認済み</p>
        </div>
    @endif
</div>
@endsection
