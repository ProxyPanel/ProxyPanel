@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">
                    {!! trans('admin.logs.user_ip.title') !!}
                </h3>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-1 col-sm-4">
                        <input type="number" class="form-control" name="id" value="{{Request::query('id')}}" placeholder="{{ trans('model.user.id') }}"/>
                    </div>
                    <div class="form-group col-lg-3 col-sm-8">
                        <input type="text" class="form-control" name="username" value="{{Request::query('username')}}" placeholder="{{ trans('common.account') }}"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <input type="text" class="form-control" name="wechat" value="{{Request::query('wechat')}}" placeholder="{{ trans('model.user.wechat') }}"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <input type="number" class="form-control" name="qq" value="{{Request::query('qq')}}" placeholder="{{ trans('model.user.qq') }}"/>
                    </div>
                    <div class="form-group col-lg-1 col-sm-6">
                        <input type="number" class="form-control" name="port" value="{{Request::query('port')}}" placeholder="{{ trans('model.user.port') }}"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button type="submit" class="btn btn-primary">{{ trans('common.search') }}</button>
                        <a href="{{route('admin.log.ip')}}" class="btn btn-danger">{{ trans('common.reset') }}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> {{ trans('common.account') }}</th>
                        <th> {{ trans('model.user.port') }}</th>
                        <th> {{ trans('model.user.account_status') }}</th>
                        <th> {{ trans('model.user.proxy_status') }}</th>
                        <th> {{ trans('admin.logs.user_ip.connect') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($userList as $user)
                        <tr>
                            <td> {{$user->id}} </td>
                            <td> {{$user->username}} </td>
                            <td> {{$user->port}} </td>
                            <td>
                                @if ($user->status > 0)
                                    <span class="badge badge-lg badge-success">{{ trans('common.status.normal') }}</span>
                                @elseif ($user->status < 0)
                                    <span class="badge badge-lg badge-danger">{{ trans('common.status.banned') }}</span>
                                @else
                                    <span class="badge badge-lg badge-default">{{ trans('common.status.inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($user->enable)
                                    <span class="badge badge-lg badge-success">{{ trans('common.status.enabled') }}</span>
                                @else
                                    <span class="badge badge-lg badge-danger">{{ trans('common.status.banned') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($user->onlineIPList->isNotEmpty())
                                    <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                                        <thead>
                                        <tr>
                                            <th> {{ trans('model.node.attribute') }}</th>
                                            <th> {{ trans('model.ip.network_type') }}</th>
                                            <th> IP</th>
                                            <th> {{ trans('validation.attributes.time') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($user->onlineIPList as $log)
                                            <tr>
                                                <td>{{$log->node->name ?? '【'.trans('common.deleted_item', ['attribute' => trans('model.node.attribute')]).'】'}}</td>
                                                <td>{{$log->type}}</td>
                                                <td>
                                                    <a href="https://db-ip.com/{{$log->ip}}" target="_blank">{{$log->ip}}</a>
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
                        {!! trans('admin.logs.counts', ['num' => $userList->total()]) !!}
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
