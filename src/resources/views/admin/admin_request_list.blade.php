@extends('layouts.admin')

@section('css')
    @vite(['resources/css/admin_request_list.css'])
@endsection

@section('content')
<div class="requestListMain">
    <div class="requestListForm">
        <main class="request-management">
            <h1 class="page-title">
                申請一覧
                @if(session('success_message'))
                    <span class="success-message">
                        {{ session('success_message') }}
                    </span>
                @endif
            </h1>

            <div class="tab-navigation">
                <a href="?tab=pending" class="tab-item {{ request('tab') !== 'approved' ? 'is-active' : '' }}">承認待ち</a>
                <a href="?tab=approved" class="tab-item {{ request('tab') === 'approved' ? 'is-active' : '' }}">承認済み</a>
            </div>

            <div class="table-wrapper">
                @if(request('tab') !== 'approved')
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
                                @foreach ($pendingRequests as $request)
                                    <tr>
                                        <td>承認待ち</td>
                                        <td>{{ $request->user->name ?? '' }}</td>
                                        <td>{{ $request->attendanceRecord->date ?? '' }}</td>
                                        <td>{{ $request->requested_comment ?? '' }}</td>
                                        <td>{{ $request->created_at ? $request->created_at->format('Y/m/d') : '' }}</td>
                                        <td>
                                            <a class="button-link" href="{{ route('admin.request.approve', ['attendance_correct_request_id' => $request->id]) }}">詳細</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                @endif

                @if(request('tab') === 'approved')
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
                                        <td>{{ $request->user->name ?? '' }}</td>
                                        <td>{{ $request->attendanceRecord->date ?? '' }}</td>
                                        <td>{{ $request->requested_comment ?? '' }}</td>
                                        <td>{{ $request->created_at ? $request->created_at->format('Y/m/d') : '' }}</td>
                                        <td>
                                            <a class="button-link" href="{{ route('admin.request.approve', ['attendance_correct_request_id' => $request->id]) }}">詳細</a>
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
