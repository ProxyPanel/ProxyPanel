@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">
                    {!! trans('admin.logs.ip_monitor') !!}
                </h2>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-2 col-sm-2">
                        <input type="number" class="form-control" name="id" value="{{Request::query('id')}}"
                               placeholder="{{ trans('model.user.id') }}"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-5">
                        <input type="text" class="form-control" name="username" value="{{Request::query('username')}}"
                               placeholder="{{ trans('common.account') }}"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-5">
                        <input type="text" class="form-control" name="ip" value="{{Request::query('ip')}}"
                               placeholder="IP"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-3">
                        <input type="number" class="form-control" name="port" value="{{Request::query('port')}}"
                               placeholder="{{ trans('model.user.port') }}"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-5">
                        <select name="node_id" id="node_id" class="form-control" onchange="this.form.submit()">
                            <option value="" hidden>{{ trans('model.node.attribute') }}</option>
                            @foreach($nodes as $node)
                                <option value="{{$node->id}}">{{$node->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4 btn-group">
                        <button type="submit" class="btn btn-primary">{{ trans('common.search') }}</button>
                        <a href="{{route('admin.log.online')}}" class="btn btn-danger">{{ trans('common.reset') }}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> {{ trans('model.ip.network_type') }}</th>
                        <th> {{ trans('model.node.attribute') }}</th>
                        <th> {{ trans('common.account') }}</th>
                        <th> IP</th>
                        <th> {{ trans('model.ip.info') }}</th>
                        <th> {{ trans('validation.attributes.time') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($onlineIPLogs as $log)
                        <tr>
                            <td>{{$log->id}}</td>
                            <td>{{$log->type}}</td>
                            <td>{{$log->node->name ?? '【'.trans('common.deleted_item', ['attribute' => trans('model.node.attribute')]).'】'}}</td>
                            <td>{{$log->user->username ?? '【'.trans('common.deleted_item', ['attribute' => trans('model.user.attribute')]).'】'.'$log->user_id'}} </td>
                            <td>
                                @if (str_contains($log->ip, ','))
                                    @foreach (explode(',', $log->ip) as $ip)
                                        <a href="https://db-ip.com/{{$ip}}" target="_blank">{{$ip}}</a>
                                    @endforeach
                                @else
                                    <a href="https://db-ip.com/{{$log->ip}}" target="_blank">{{$log->ip}}</a>
                                @endif
                            </td>
                            <td>{{$log->ipInfo?? ''}}</td>
                            <td>{{date('Y-m-d H:i:s',$log->created_at)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.logs.counts', ['num' => $onlineIPLogs->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$onlineIPLogs->links()}}
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
    <script>
      $(document).ready(function() {
        $('#node_id').val({{Request::query('node_id')}});
      });
    </script>
@endsection
