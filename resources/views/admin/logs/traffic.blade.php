@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{{ trans('admin.logs.user_traffic.title') }}</h2>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-2 col-sm-4">
                        <input type="number" class="form-control" name="user_id" value="{{Request::query('user_id')}}" placeholder="{{ trans('model.user.id') }}"/>
                    </div>
                    <div class="form-group col-lg-3 col-sm-8">
                        <input type="text" class="form-control" name="username" value="{{Request::query('username')}}" placeholder="{{ trans('common.account') }}"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4">
                        <input type="number" class="form-control" name="port" value="{{Request::query('port')}}" placeholder="{{ trans('model.user.port') }}"/>
                    </div>
                    <div class="form-group col-lg-3 col-sm-8">
                        <select class="form-control" name="node_id" id="node_id" onchange="this.form.submit()">
                            <option value="" hidden>{{ trans('admin.logs.user_traffic.choose_node') }}</option>
                            @foreach($nodes as $node)
                                <option value="{{$node->id}}">{{$node->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-lg-6 col-sm-12">
                        <div class="input-group input-daterange" data-plugin="datepicker">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="icon wb-calendar" aria-hidden="true"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" name="start" value="{{Request::query('start')}}" autocomplete="off"/>
                            <div class="input-group-prepend">
                                <span class="input-group-text">{{ trans('common.to') }}</span>
                            </div>
                            <input type="text" class="form-control" name="end" value="{{Request::query('end')}}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4 btn-group">
                        <button type="submit" class="btn btn-primary">{{ trans('common.search') }}</button>
                        <a href="{{route('admin.log.traffic')}}" class="btn btn-danger">{{ trans('common.reset') }}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> {{ trans('common.account') }}</th>
                        <th> {{ trans('model.node.attribute') }}</th>
                        <th> {{ trans('model.node.data_rate') }}</th>
                        <th> {{ trans('model.user_traffic.upload') }}</th>
                        <th> {{ trans('model.user_traffic.download') }}</th>
                        <th> {{ trans('model.user_traffic.total') }}</th>
                        <th> {{ trans('model.user_traffic.log_time') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dataFlowLogs as $log)
                        <tr>
                            <td> {{$log->id}} </td>
                            <td>
                                @if(empty($log->user))
                                    【{{trans('common.deleted_item', ['attribute' => trans('common.account')])}}】
                                @else
                                    @can('admin.user.index')
                                        <a href="{{route('admin.user.index', ['id' => $log->user->id])}}" target="_blank"> {{$log->user->username}} </a>
                                    @else
                                        {{$log->user->username}}
                                    @endcan
                                @endif
                            </td>
                            <td> {{$log->node->name ?? '【'.trans('common.deleted_item', ['attribute' => trans('model.node.attribute')]).'】'}} </td>
                            <td> {{$log->rate}} </td>
                            <td> {{$log->u}} </td>
                            <td> {{$log->d}} </td>
                            <td><span class="badge badge-danger"> {{$log->traffic}} </span></td>
                            <td> {{$log->log_time}} </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-6">
                        {!! trans('admin.logs.counts', ['num' => $dataFlowLogs->total()]) !!} |
                        <code>{{$totalTraffic}}</code>
                    </div>
                    <div class="col-sm-6">
                        <nav class="Page navigation float-right">
                            {{$dataFlowLogs->links()}}
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
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script>
      $('.input-daterange').datepicker({
        format: 'yyyy-mm-dd',
      });

      $(document).ready(function() {
        $('#node_id').val({{Request::query('node_id')}});
      });
    </script>
@endsection
