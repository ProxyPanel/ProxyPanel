@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <style>
        .table th a {
            color: #76838f;
            text-decoration: none;
        }
    </style>
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('admin.menu.user.subscribe') }}</h3>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-1 col-sm-6">
                        <input type="number" class="form-control" name="user_id" value="{{Request::query('user_id')}}" placeholder="{{ trans('model.user.id') }}"/>
                    </div>
                    <div class="form-group col-lg-3 col-sm-6">
                        <input type="text" class="form-control" name="username" value="{{Request::query('username')}}" placeholder="{{ trans('model.user.username') }}"/>
                    </div>
                    <div class="form-group col-lg-3 col-sm-6">
                        <input type="text" class="form-control" name="code" value="{{Request::query('code')}}" placeholder="{{ trans('model.subscribe.code') }}"/>
                    </div>
                    <div class="form-group col-lg-3 col-sm-6">
                        <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                            <option value="" hidden>{{ trans('common.status.attribute') }}</option>
                            <option value="0">{{ trans('common.status.banned') }}</option>
                            <option value="1">{{ trans('common.status.normal') }}</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button type="submit" class="btn btn-primary">{{ trans('common.search') }}</button>
                        <a href="{{route('admin.subscribe.index')}}" class="btn btn-danger">{{ trans('common.reset') }}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> @sortablelink('id', '#')</th>
                        <th> {{ trans('model.user.username') }}</th>
                        <th> {{ trans('model.subscribe.code') }}</th>
                        <th> @sortablelink('times', trans('model.subscribe.req_times'))</th>
                        <th> {{ trans('model.subscribe.updated_at') }}</th>
                        <th> {{ trans('model.subscribe.ban_time') }}</th>
                        <th> {{ trans('model.subscribe.ban_desc') }}</th>
                        <th> {{trans('common.action')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($subscribeList as $subscribe)
                        <tr>
                            <td> {{$subscribe->id}} </td>
                            <td>
                                @if($subscribe->has('user'))
                                    @can('admin.user.index')
                                        <a href="{{route('admin.user.index', ['id'=>$subscribe->user->id])}}" target="_blank">{{$subscribe->user->username}}</a>
                                    @else
                                        {{$subscribe->user->username}}
                                    @endcan
                                @else
                                    【{{trans('common.deleted_item', ['attribute' => trans('common.account')])}}】
                                @endif
                            </td>
                            <td> {{$subscribe->code}} </td>
                            <td>
                                @can('admin.subscribe.log')
                                    <a href="{{route('admin.subscribe.log', $subscribe)}}" target="_blank">{{$subscribe->times}}</a>
                                @endcan
                            </td>
                            <td> {{$subscribe->updated_at}} </td>
                            <td> {{$subscribe->ban_time ? date('Y-m-d H:i', $subscribe->ban_time): ''}} </td>
                            <td> {{ __($subscribe->ban_desc) }} </td>
                            <td>
                                @can('admin.subscribe.set')
                                    <button class="btn btn-sm @if($subscribe->status === 0) btn-outline-success @else btn-outline-danger @endif"
                                            onclick="setSubscribeStatus('{{route('admin.subscribe.set', $subscribe)}}')">
                                        @if($subscribe->status === 0)
                                            {{ trans('common.status.enabled') }}
                                        @else
                                            {{ trans('common.status.banned') }}
                                        @endif
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.logs.counts', ['num' => $subscribeList->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$subscribeList->links()}}
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
        $('#status').val({{Request::query('status')}});
      });

      @can('admin.subscribe.set')
      // 启用禁用用户的订阅
      function setSubscribeStatus(url) {
        $.post(url, {_token: '{{csrf_token()}}'}, function(ret) {
          if (ret.status === 'success') {
            swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => {
              window.location.reload();
            });
          } else {
            swal.fire({title: ret.message, icon: 'error', timer: 1000, showConfirmButton: false}).then(() => {
              window.location.reload();
            });
          }
        });
      }
        @endcan
    </script>
@endsection
