@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">用户在线IP列表
                    <small>最近10分钟</small>
                </h3>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-1 col-sm-4">
                        <input type="number" class="form-control" name="id" value="{{Request::query('id')}}" placeholder="ID"/>
                    </div>
                    <div class="form-group col-lg-3 col-sm-8">
                        <input type="text" class="form-control" name="email" value="{{Request::query('email')}}" placeholder="用户账号"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <input type="text" class="form-control" name="wechat" value="{{Request::query('wechat')}}" placeholder="微信"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <input type="number" class="form-control" name="qq" value="{{Request::query('qq')}}" placeholder="QQ"/>
                    </div>
                    <div class="form-group col-lg-1 col-sm-6">
                        <input type="number" class="form-control" name="port" value="{{Request::query('port')}}" placeholder="端口"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button type="submit" class="btn btn-primary">搜 索</button>
                        <a href="{{route('admin.log.ip')}}" class="btn btn-danger">{{trans('common.reset')}}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 用户账号</th>
                        <th> 端口</th>
                        <th> {{trans('common.status')}}</th>
                        <th> 代理</th>
                        <th> 连接IP</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($userList as $user)
                        <tr>
                            <td> {{$user->id}} </td>
                            <td> {{$user->email}} </td>
                            <td> {{$user->port}} </td>
                            <td>
                                @if ($user->status > 0)
                                    <span class="badge badge-lg badge-success">正常</span>
                                @elseif ($user->status < 0)
                                    <span class="badge badge-lg badge-danger">禁用</span>
                                @else
                                    <span class="badge badge-lg badge-default">未激活</span>
                                @endif
                            </td>
                            <td>
                                @if ($user->enable)
                                    <span class="badge badge-lg badge-success">启用</span>
                                @else
                                    <span class="badge badge-lg badge-danger">禁用</span>
                                @endif
                            </td>
                            <td>
                                @if($user->onlineIPList->isNotEmpty())
                                    <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                                        <thead>
                                        <tr>
                                            <th> 节点</th>
                                            <th> 类型</th>
                                            <th> IP</th>
                                            <th> 时间</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($user->onlineIPList as $log)
                                            <tr>
                                                <td>{{$log->node->name ?? '【节点已删除】'}}</td>
                                                <td>{{$log->type}}</td>
                                                <td>
                                                    <a href="https://www.ipip.net/ip/{{$log->ip}}.html" target="_blank">{{$log->ip}}</a>
                                                </td>
                                                <td>{{date('Y-m-d H:i:s', $log->created_at)}}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$userList->total()}}</code> 个账号
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$userList->links()}}
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
