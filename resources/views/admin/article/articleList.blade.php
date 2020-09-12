@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">文章列表</h3>
                <div class="panel-actions">
                    <a href="/admin/addArticle" class="btn btn-primary"><i class="icon wb-plus"></i>添加文章</a>
                </div>
            </div>
            <div class="panel-body">
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 类型</th>
                        <th> 标题</th>
                        <th> 排序</th>
                        <th> 发布日期</th>
                        <th> 操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($list as $vo)
                        <tr>
                            <td> {{$vo->id}} </td>
                            @if ($vo->type == '1')
                                <td> 文章</td>
                            @elseif ($vo->type == '2')
                                <td> 公告</td>
                            @elseif ($vo->type == '3')
                                <td> 购买说明</td>
                            @elseif ($vo->type == '4')
                                <td> 使用教程</td>
                            @else
                                <td> 未知</td>
                            @endif
                            <td>
                                <a href="/article?id={{$vo->id}}"
                                        target="_blank"> {{Str::limit($vo->title, 80)}} </a>
                            </td>
                            <td> {{$vo->sort}} </td>
                            <td> {{$vo->created_at}} </td>
                            <td>
                                <div class="btn-group">
                                    <a href="/admin/editArticle?id={{$vo->id}}&page={{Request::get('page', 1)}}"
                                            class="btn btn-outline-primary"><i class="icon wb-edit"></i></a>
                                    <button class="btn btn-outline-danger" onclick="delArticle('{{$vo->id}}')"><i
                                                class="icon wb-close"></i></button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$list->total()}}</code> 篇文章
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
@section('script')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"
            type="text/javascript"></script>
    <script type="text/javascript">
      // 删除文章
      function delArticle(id) {
        swal.fire({
          title: '确定删除文章?',
          type: 'question',
          showCancelButton: true,
          cancelButtonText: '{{trans('home.ticket_close')}}',
          confirmButtonText: '{{trans('home.ticket_confirm')}}',
        }).then((result) => {
          if (result.value) {
            $.post('/admin/delArticle', {id: id, _token: '{{csrf_token()}}'}, function(ret) {
              if (ret.status === 'success') {
                swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false}).
                    then(() => window.location.reload());
              }
              else {
                swal.fire({title: ret.message, type: 'error'}).then(() => window.location.reload());
              }
            });
          }
        });
      }
    </script>
@endsection
