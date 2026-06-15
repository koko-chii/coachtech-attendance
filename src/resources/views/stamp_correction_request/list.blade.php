@extends('layouts.app')

@section('css')
    <link rel="preconnect" href="https://googleapis.com">
    <link rel="preconnect" href="https://gstatic.com" crossorigin>
    <link href="https://googleapis.com/css2?family=M+PLUS+1p:wght@400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/stamp_correction_list.css']) 
@endsection

@section('content')
<div class="requestListMain">
    <div class="requestListForm">
        <main class="request-management">
            <!-- ページタイトル -->
            <h1 class="page-title">申請一覧</h1>

            <!-- タブナビゲーション（クエリパラメータで切り替え） -->
            <div class="tab-navigation">
                <a href="?tab=pending" class="tab-item {{ request('tab', 'pending') === 'pending' ? 'is-active' : '' }}">承認待ち</a>
                <a href="?tab=approved" class="tab-item {{ request('tab') === 'approved' ? 'is-active' : '' }}">承認済み</a>
            </div>

            <!-- テーブルコンテナエリア -->
            <div class="table-wrapper">
                @if(request('tab', 'pending') === 'pending')
                    <!-- 承認待ち一覧 -->
                    @if($pendingRequests->isEmpty())
                        <p class="empty-message">承認待ちの申請はありません。</p>
                    @else
                        <table class="table-request">
                            <thead>
                                <tr>
                                    <th scope="col">状態</th>
                                    <th scope="col">名前</th>
                                    <th scope="col">対象日時</th>
                                    <th scope="col">申請理由</th>
                                    <th scope="col">申請日時</th>
                                    <th scope="col">詳細</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingRequests as $request)
                                    <tr>
                                        <td>承認待ち</td>
                                        <!-- 💡リレーション名（user や attendanceRecord）に合わせて調整してください -->
                                        <td>{{ $request->user->name ?? ($request->attendanceRecord->user->name ?? '一般ユーザー') }}</td>
                                        <td>{{ $request->target_date ?? ($request->attendanceRecord->date ?? '') }}</td>
                                        <td>{{ $request->reason }}</td>
                                        <td>{{ $request->created_at->format('Y/m/d') }}</td>
                                        <td>
                                            <a href="{{ route('attendance.detail', $request->attendance_record_id) }}" class="button-link">詳細</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                @else
                    <!-- 承認済み一覧 -->
                    @if($approvedRequests->isEmpty())
                        <p class="empty-message">承認済みの申請はありません。</p>
                    @else
                        <table class="table-request">
                            <thead>
                                <tr>
                                    <th scope="col">状態</th>
                                    <th scope="col">名前</th>
                                    <th scope="col">対象日時</th>
                                    <th scope="col">申請理由</th>
                                    <th scope="col">申請日時</th>
                                    <th scope="col">詳細</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($approvedRequests as $request)
                                    <tr>
                                        <td>承認済み</td>
                                        <td>{{ $request->user->name ?? ($request->attendanceRecord->user->name ?? '一般ユーザー') }}</td>
                                        <td>{{ $request->target_date ?? ($request->attendanceRecord->date ?? '') }}</td>
                                        <td>{{ $request->reason }}</td>
                                        <td>{{ $request->created_at->format('Y/m/d') }}</td>
                                        <td>
                                            <a href="{{ route('attendance.detail', $request->attendance_record_id) }}" class="button-link">詳細</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                @endif
            </div>
        </main>
    </div>
</div>
@endsection
