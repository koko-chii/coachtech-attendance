@extends('layouts.app')<!-- ヘッダーの親ファイルとCSS継承 -->

<!-- 専用のCSSを読み込み Vite(高速なフロントエンド構築ツール) -->
@section('css')
    @vite(['resources/css/attendance_detail.css'])
@endsection

<!-- 親ファイルのcontentスペースに流し込む -->
@section('content')
    <div class="attendanceDetailMain">
        <div class="attendanceDetailForm">

            <h1 class="attendanceDetailTitle">勤怠詳細</h1>

            <!-- もし承認待ちなら承認待ちのため修正はできませんと表示 -->
            @if($record->status === '承認待ち')
                <p class="alertMessage">承認待ちのため修正はできません。</p>
            @endif

            <main>
                <!-- ユーザーの勤怠情報を修正するための仕組み -->
                <form action="{{ route('attendance.update', ['id' => $record->id]) }}" method="POST">
                    <!-- cookie自動送信を悪用した攻撃を防ぐための@csrf(セキュリティトークン) -->
                    @csrf
                    <!-- HTMLではPOSTだがLaravelの更新はPATCHのため -->
                    @method('PATCH')

                    <!-- 勤怠詳細テーブル -->
                    <table class="attendanceTable">
                        <tbody>
                            <tr>
                                <th>名前</th>
                                <td>{{ auth()->user()->name }}</td>
                            </tr>
                                                        <tr>
                                <th>日付</th>
                                <td>
                                    <div class="dateDisplayGroup">
                                        <span class="dateYearText">{{ \Carbon\Carbon::parse($record->date)->format('Y年') }}</span>
                                        <span class="dateDayText">{{ \Carbon\Carbon::parse($record->date)->format('n月j日') }}</span>
                                    </div>
                                </td>
                            </tr>

                                <th>出勤・退勤</th>
                                <td>
                                    <div class="timeRangeGroup">
                                        <!-- 出退勤時刻の修正申請入力欄 承認待ち申請詳細は修正不可-->
                                        <input type="time" name="clock_in" class="inputTimeField" value="{{ \Carbon\Carbon::parse($record->clock_in)->format('H:i') }}" {{ $record->status === '承認待ち' ? 'disabled' : '' }}>
                                        <span class="timeSeparator">〜</span>
                                        <input type="time" name="clock_out" class="inputTimeField" value="{{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('H:i') : '' }}" {{ $record->status === '承認待ち' ? 'disabled' : '' }}>
                                    </div>
                                </td>
                            </tr>

                            <!-- 休憩時刻の修正申請入力欄　承認待ち申請詳細は修正不可 -->
                            @foreach($record->breakLogs as $index => $break)
                                <tr>
                                    <!-- データー順が0から開始のため -->
                                    <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                                    <td>
                                        <div class="timeRangeGroup">
                                            <!-- 1回目の休憩と2回目の休憩に番号の名前をつけてコントローラーへ送る(休憩回数分繰り返す) -->
                                            <input type="time" name="breaks[{{ $index }}][break_in]" class="inputTimeField" value="{{ \Carbon\Carbon::parse($break->break_in)->format('H:i') }}" {{ $record->status === '承認待ち' ? 'disabled' : '' }}>
                                            <span class="timeSeparator">〜</span>
                                            <input type="time" name="breaks[{{ $index }}][break_out]" class="inputTimeField" value="{{ $break->break_out ? \Carbon\Carbon::parse($break->break_out)->format('H:i') : '' }}" {{ $record->status === '承認待ち' ? 'disabled' : '' }}>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            <!-- もし承認待ちでないなら新たな変更申請できるよう休憩(追加)を表示 -->
                            @if($record->status !== '承認待ち')
                                <tr>
                                    <th>休憩{{ count($record->breakLogs) + 1 }}</th>
                                    <td>
                                        <!-- 休憩2の入力欄は空欄の場合、何も表示しない（クリック時にtime型へ切り替える仕組み） -->
                                        <div class="timeRangeGroup">
                                            <input type="text" name="new_break_in" class="inputTimeField" onfocus="this.type='time'" onblur="if(!this.value)this.type='text'">
                                            <span class="timeSeparator">〜</span>
                                            <input type="text" name="new_break_out" class="inputTimeField" onfocus="this.type='time'" onblur="if(!this.value)this.type='text'">
                                        </div>
                                    </td>
                                </tr>
                            @endif

                            <tr>
                                <!-- 備考入力欄 -->
                                <th>備考</th>
                                <td>
                                    <!-- ステータスが承認待ちなら書き換え禁止 -->
                                    <!-- 見本通り、上下のスクロール矢印が出ない、もう1行分広いスッキリした空欄ボックスにするためにinput型に変更（不要な閉じタグも完全消去） -->
                                    <input type="text" name="remarks" class="textareaRemarksField" value="{{ $record->remarks }}" {{ $record->status === '承認待ち' ? 'disabled' : '' }}>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- 承認待ちでないなら修正ボタン押下可能 -->
                    @if($record->status !== '承認待ち')
                        <div class="formActionsPanel">
                            <button type="submit" class="submitUpdateButton">修正</button>
                        </div>
                    @endif
                </form>
            </main>
        </div>
    </div>
@endsection
