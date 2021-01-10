@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">订阅列表</h3>
            </div>
            <div class="panel-body">
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 用户</th>
                        <th> 请求IP</th>
                        <th> 归属地</th>
                        <th> 请求时间</th>
                        <th> 访问</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($subscribeLog as $subscribe)
                        <tr>
                            <td>{{$subscribe->id}}</td>
                            <td>{{$subscribe->user->email ?? '用户已删除'}}</td>
                            <td>
                                @if ($subscribe->request_ip)
                                    <a href="https://www.ipip.net/ip/{{$subscribe->request_ip}}.html" target="_blank">{{$subscribe->request_ip}}</a>
                                @endif
                            </td>
                            <td>
                                {{$subscribe->ipInfo}}
                            </td>
                            <td>{{$subscribe->request_time}}</td>
                            <td>{{$subscribe->request_header}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$subscribeLog->total()}}</code> 条记录
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$subscribeLog->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
@endsection
