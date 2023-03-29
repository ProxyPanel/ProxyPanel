@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{{ trans('admin.logs.notification') }}</h2>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-3 col-sm-4">
                        <input type="text" class="form-control" name="username" value="{{Request::query('username')}}" placeholder="{{ trans('common.account') }}"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4">
                        <select class="form-control" name="type" id="type" onchange="this.form.submit()">
                            <option value="" hidden>{{ trans('model.common.type') }}</option>
                            @foreach(config('common.notification.labels') as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-lg-1 col-sm-4 btn-group">
                        <button type="submit" class="btn btn-primary">{{ trans('common.search') }}</button>
                        <a href="{{route('admin.log.notify')}}" class="btn btn-danger">{{ trans('common.reset') }}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> {{ trans('model.common.type') }}</th>
                        <th> {{ trans('model.notification.address') }}</th>
                        <th> {{ trans('validation.attributes.title') }}</th>
                        <th> {{ trans('validation.attributes.content') }}</th>
                        <th> {{ trans('model.notification.created_at') }}</th>
                        <th> {{ trans('model.notification.status') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($notificationLogs as $log)
                        <tr>
                            <td> {{$log->id}} </td>
                            <td> {{$log->type_label}} </td>
                            <td> {{$log->address}} </td>
                            <td> {{$log->title}} </td>
                            <td class="text-break"> {{$log->content}} </td>
                            <td> {{$log->created_at}} </td>
                            <td>
                                @if($log->status < 0)
                                    <p class="badge badge-danger text-break font-size-14"> {{$log->error}} </p>
                                @elseif($log->status > 0)
                                    <labe class="badge badge-success">{{ trans('common.success') }}</labe>
                                @else
                                    <span class="badge badge-default"> {{ trans('common.status.waiting_tobe_send') }} </span>
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
                        {!! trans('admin.logs.counts', ['num' => $notificationLogs->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$notificationLogs->links()}}
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
        $('#type').val({{Request::query('type')}});
      });
    </script>
@endsection
