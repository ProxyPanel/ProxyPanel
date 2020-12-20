@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">邮件投递记录</h2>
            </div>
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group col-lg-3 col-sm-4">
                        <input type="text" class="form-control" name="email" id="email"
                               value="{{Request::input('email')}}" placeholder="用户名"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4">
                        <select class="form-control" name="type" id="type" onChange="Search()">
                            <option value="" hidden>类型</option>
                            <option value="1">邮件</option>
                            <option value="2">ServerChan</option>
                            <option value="3">Bark</option>
                            <option value="4">Telegram</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-1 col-sm-4 btn-group">
                        <button class="btn btn-primary" onclick="Search()">搜 索</button>
                        <a href="{{route('admin.log.notify')}}" class="btn btn-danger">重 置</a>
                    </div>
                </div>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 类型</th>
                        <th> 收信地址</th>
                        <th> 标题</th>
                        <th> 内容</th>
                        <th> 投递时间</th>
                        <th> 投递状态</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($list as $vo)
                        <tr>
                            <td> {{$vo->id}} </td>
                            <td> {{$vo->type === 1 ? 'Email' : ($vo->type === 2? 'ServerChan': 'Bark')}} </td>
                            <td> {{$vo->address}} </td>
                            <td> {{$vo->title}} </td>
                            <td> {{$vo->content}} </td>
                            <td> {{$vo->created_at}} </td>
                            <td>
                                @if($vo->status < 0)
                                    <span class="badge badge-danger"> {{Str::limit($vo->error)}} </span>
                                @elseif($vo->status > 0)
                                    <labe class="badge badge-success">投递成功</labe>
                                @else
                                    <span class="badge badge-default"> 等待投递 </span>
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
      $(document).ready(function() {
        $('#type').val({{Request::input('type')}});
      });

      // 搜索
      function Search() {
        window.location.href = '{{route('admin.log.notify')}}?email=' + $('#email').val() + '&type=' +
            $('#type option:selected').val();
      }
    </script>
@endsection
