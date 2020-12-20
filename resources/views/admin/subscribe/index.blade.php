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
                <div class="form-row">
                    <div class="form-group col-lg-2 col-sm-6">
                        <input type="number" class="form-control" name="user_id" id="user_id"
                               value="{{Request::input('user_id')}}" placeholder="ID"/>
                    </div>
                    <div class="form-group col-lg-4 col-sm-6">
                        <input type="text" class="form-control" name="email" id="email"
                               value="{{Request::input('email')}}" placeholder="用户名"/>
                    </div>
                    <div class="form-group col-lg-3 col-sm-6">
                        <select name="status" id="status" class="form-control" onChange="Search()">
                            <option value="" hidden>状态</option>
                            <option value="0">禁用</option>
                            <option value="1">正常</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button class="btn btn-primary" onclick="Search()">搜 索</button>
                        <a href="{{route('admin.subscribe.index')}}" class="btn btn-danger">重 置</a>
                    </div>
                </div>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 用户</th>
                        <th> 订阅码</th>
                        <th> 请求次数</th>
                        <th> 最后请求时间</th>
                        <th> 封禁时间</th>
                        <th> 封禁理由</th>
                        <th> 操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($subscribeList as $subscribe)
                        <tr>
                            <td> {{$subscribe->id}} </td>
                            <td>
                                @if(empty($subscribe->user))
                                    【账号已删除】
                                @else
                                    @can('admin.user.index')
                                        <a href="{{route('admin.user.index', ['id'=>$subscribe->user->id])}}" target="_blank">{{$subscribe->user->email}}</a>
                                    @else
                                        {{$subscribe->user->email}}
                                    @endcan
                                @endif
                            </td>
                            <td> {{$subscribe->code}} </td>
                            <td>
                                @can('admin.subscribe.log')
                                    <a href="{{route('admin.subscribe.log', $subscribe->id)}}" target="_blank">{{$subscribe->times}}</a>
                                @endcan
                            </td>
                            <td> {{$subscribe->updated_at}} </td>
                            <td> {{$subscribe->ban_time ? date('Y-m-d H:i', $subscribe->ban_time): ''}} </td>
                            <td> {{$subscribe->ban_desc}} </td>
                            <td>
                                @can('admin.subscribe.set')
                                    <button class="btn btn-sm @if($subscribe->status == 0) btn-outline-success @else btn-sm btn-outline-danger @endif"
                                            onclick="setSubscribeStatus('{{route('admin.subscribe.set', $subscribe->id)}}')">
                                        @if($subscribe->status == 0) 启用 @else 禁用 @endif
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
                        共 <code>{{$subscribeList->total()}}</code> 条记录
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
        $('#status').val({{Request::input('status')}});
      });

      //回车检测
      $(document).on('keypress', 'input', function(e) {
        if (e.which === 13) {
          Search();
          return false;
        }
      });

      // 搜索
      function Search() {
        window.location.href = '{{route('admin.subscribe.index')}}' + '?user_id=' + $('#user_id').val() + '&email=' + $('#email').val() + '&status=' +
            $('#status option:selected').val();
      }

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
