@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">余额变动记录</h3>
            </div>
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group col-lg-3 col-sm-6">
                        <input type="text" class="form-control" name="email" id="email" value="{{Request::input('email')}}" placeholder="用户名"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button class="btn btn-primary" onclick="Search()">搜 索</button>
                        <a href="{{route('admin.log.credit')}}" class="btn btn-danger">重 置</a>
                    </div>
                </div>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 用户名</th>
                        <th> 订单ID</th>
                        <th> 操作前余额</th>
                        <th> 发生金额</th>
                        <th> 操作后金额</th>
                        <th> 描述</th>
                        <th> 发生时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($list as $vo)
                        <tr>
                            <td> {{$vo->id}} </td>
                            <td>
                                @if(empty($vo->user))
                                    【账号已删除】
                                @else
                                    <a href="{{route('admin.log.credit', ['email'=>$vo->user->email])}}"> {{$vo->user->email}} </a>
                                @endif
                            </td>
                            <td> {{$vo->order_id}} </td>
                            <td> {{$vo->before}} </td>
                            <td> {{$vo->amount}} </td>
                            <td> {{$vo->after}} </td>
                            <td> {{$vo->description}} </td>
                            <td> {{$vo->created_at}} </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$list->total()}}</code> 条记录
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$list->links()}}
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
      //回车检测
      $(document).on('keypress', 'input', function(e) {
        if (e.which === 13) {
          Search();
          return false;
        }
      });

      // 搜索
      function Search() {
        window.location.href = '{{route('admin.log.credit')}}?email=' + $('#email').val();
      }
    </script>
@endsection
