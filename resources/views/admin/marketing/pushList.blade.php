@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-markdown/bootstrap-markdown.min.css" rel="stylesheet">
    <link href="/assets/global/fonts/font-awesome/font-awesome.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">推送消息列表</h3>
                @can('admin.marketing.add')
                    <div class="panel-actions">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#send_modal"><i class="icon wb-plus"></i>推送消息</button>
                    </div>
                @endcan
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
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button class="btn btn-primary" onclick="Search()">搜 索</button>
                        <a href="{{route('admin.marketing.push')}}" class="btn btn-danger">重 置</a>
                    </div>
                </div>
                <div class="alert alert-info alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    仅会推送给关注了您的消息通道的用户 @can('admin.system.index')<a href="{{route('admin.system.index')}}" class="alert-link" target="_blank">设置PushBear</a> @else 设置PushBear @endcan
                </div>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 消息标题</th>
                        <th> 消息内容</th>
                        <th> 推送状态</th>
                        <th> 推送时间</th>
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
                        共 <code>{{$list->total()}}</code> 条推送消息
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

    @can('admin.marketing.add')
        <!-- 推送消息 -->
        <div id="send_modal" class="modal fade" tabindex="-1" data-focus-on="input:first" data-backdrop="static"
             data-keyboard="false">
            <div class="modal-dialog modal-lg  modal-center">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title">推送消息</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" style="display: none;" id="msg"></div>
                        <form action="#" method="post" class="form-horizontal">
                            <div class="form-body">
                                <div class="form-group">
                                    <div class="row">
                                        <label for="title" class="col-md-2 control-label"> 标题 </label>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" name="title" id="title"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="content" class="col-md-2 control-label"> 内容 </label>
                                        <div class="col-md-9">
                                            <textarea class="form-control" rows="10" name="content" id="content" data-provide="markdown" data-iconlibrary="fa"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger" data-dismiss="modal">取消</button>
                        <button class="btn btn-primary" onclick="return send();">推送</button>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-markdown/bootstrap-markdown.min.js"></script>
    <script src="/assets/global/vendor/marked/marked.min.js"></script>
    <script>
      $(document).ready(function() {
        $('#status').val({{Request::input('status')}});
      });

      function Search() {
        window.location.href = '{{route('admin.marketing.push')}}?status=' + $('#status').val();
      }

      @can('admin.marketing.add')
      // 发送通道消息
      function send() {
        const title = $('#title').val();

        if (title.trim() === '') {
          $('#msg').show().html('标题不能为空');
          title.focus();
          return false;
        }

        $.ajax({
          url: '{{route('admin.marketing.add')}}',
          method: 'POST',
          data: {_token: '{{csrf_token()}}', title: title, content: $('#content').val()},
          beforeSend: function() {
            $('#msg').show().html('正在添加...');
          },
          success: function(ret) {
            if (ret.status === 'fail') {
              $('#msg').show().html(ret.message);
              return false;
            }

            $('#send_modal').modal('hide');

          },
          error: function() {
            $('#msg').show().html('请求错误，请重试');
          },
          complete: function() {
          },
        });
      }

      // 关闭modal触发
      $('#send_modal').on('hide.bs.modal', function() {
        window.location.reload();
      });
        @endcan
    </script>
@endsection
