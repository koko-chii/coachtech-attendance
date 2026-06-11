@extends('layouts.app')

@section('css')
    @vite(['resources/css/stamp_correction_list.css']) 
@endsection

@section('content')
<!-- 申請一覧コンテナ枠 -->
    <div class="container-fluid">
        <!-- 主要な申請管理の開始エリア -->
        <main class="request-management">
            <h1 class="page-title">申請一覧</h1>

            <!-- 承認待ち申請エリア -->
            <section class="request-section">
                <h2 class="section-title">承認待ち申請一覧</h2>
                
                <!-- もし承認待ちがなければ -->
                @if($pendingRequests->isEmpty())
                    <p class="empty-message">承認待ちの申請はありません。</p>
                @else
                    <!-- 承認待ちがあれば -->
                    <table class="table-request">
                        <thead>
                            <tr>
                                <th scope="col">申請日</th>
                                <th scope="col">申請理由</th>
                                <th scope="col">詳細</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- 年月日と詳細を繰り返し表示 -->
                            @foreach($pendingRequests as $request)
                                <tr>
                                    <td>{{ $request->created_at->format('Y-m-d') }}</td>
                                    <td>{{ $request->reason }}</td>
                                    <td>
                                        <a href="{{ route('attendance.detail', $request->attendance_record_id) }}" class="button-link">詳細</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>

            <!-- 承認済み申請エリア -->
            <section class="request-section">
                <h2 class="section-title">承認済み申請一覧</h2>
                
                <!-- もし承認済み申請がなければ -->
                @if($approvedRequests->isEmpty())
                    <p class="empty-message">承認済みの申請はありません。</p>
                <!-- 承認済み申請がある場合 -->
                @else
                    <!-- 承認済み申請を表示するテーブル（表） -->
                    <table class="table-request">
                        <thead>
                            <tr>
                                <th scope="col">申請日</th>
                                <th scope="col">申請理由</th>
                                <th scope="col">詳細</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- 承認済みデータを1件ずつ取り出してループ処理 -->
                            @foreach($approvedRequests as $request)
                                <tr>
                                    <td>{{ $request->created_at->format('Y-m-d') }}</td>
                                    <td>{{ $request->reason }}</td>
                                    <td>
                                        <a href="{{ route('attendance.detail', $request->attendance_record_id) }}" class="button-link">詳細</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>
        </main>
    </div>
@endsection
