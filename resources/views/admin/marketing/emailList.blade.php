@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">邮件群发列表</h3>
                <div class="panel-actions">
                    <button class="btn btn-primary" onclick="send()"><i class="icon wb-envelope"></i>群发邮件</button>
                </div>
            </div>
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group col-lg-3 col-sm-6">
                        <select class="form-control" name="status" id="status" onChange="Search()">
                            <option value="" hidden>状态</option>
                            <option value="0">待发送</option>
                            <option value="-1">失败</option>
                            <option value="1">成功</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-3 col-sm-6 btn-group">
                        <button class="btn btn-primary" onclick="Search()">搜 索</button>
                        <a href="{{route('admin.marketing.email')}}" class="btn btn-danger">重 置</a>
                    </div>
                </div>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 消息标题</th>
                        <th> 消息内容</th>
                        <th> 发送状态</th>
                        <th> 发送时间</th>
                        <th> 错误信息</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($list as $vo)
                        <tr>
                            <td> {{$vo->id}} </td>
                            <td> {{$vo->title}} </td>
                            <td> {{$vo->content}} </td>
                            <td> {{$vo->status_label}} </td>
                            <td> {{$vo->created_at}} </td>
                            <td> {{$vo->error}} </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$list->total()}}</code> 条消息
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
        $('#status').val({{Request::input('status')}});
      });

      // 发送邮件
      function send() {
        swal.fire('抱歉', '开发中！敬请期待', 'info');
      }

      function Search() {
        window.location.href = '{{route('admin.marketing.email')}}?status=' + $('#status option:selected').val();
      }
    </script>
@endsection
