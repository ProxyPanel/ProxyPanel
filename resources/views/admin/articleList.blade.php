@extends('admin.layouts')

@section('css')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject bold uppercase"> 文章列表 </span>
                        </div>
                        <div class="actions">
                            <div class="btn-group">
                                <button class="btn sbold blue" onclick="addArticle()"> 添加文章 </button>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable table-scrollable-borderless">
                            <table class="table table-hover table-light">
                                <thead>
                                <tr>
                                    <th> # </th>
                                    <th> 标题 </th>
                                    <th> 类型 </th>
                                    <th> 排序 </th>
                                    <th> 发布日期 </th>
                                    <th> 操作 </th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($articleList->isEmpty())
                                    <tr>
                                        <td colspan="6" style="text-align: center;">暂无数据</td>
                                    </tr>
                                @else
                                    @foreach($articleList as $article)
                                        <tr class="odd gradeX">
                                            <td> {{$article->id}} </td>
                                            <td> <a href="{{url('user/article?id=' . $article->id)}}" target="_blank"> {{str_limit($article->title, 80)}} </a> </td>
                                            <td> {{$article->type == '1' ? '文章' : '公告'}} </td>
                                            <td> {{$article->sort}} </td>
                                            <td> {{$article->created_at}} </td>
                                            <td>
                                                <button type="button" class="btn btn-sm blue btn-outline" onclick="editArticle('{{$article->id}}')">
                                                    <i class="fa fa-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm red btn-outline" onclick="delArticle('{{$article->id}}')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$articleList->total()}} 篇文章</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $articleList->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/js/layer/layer.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 添加文章
        function addArticle() {
            window.location.href = '{{url('admin/addArticle')}}';
        }

        // 编辑文章
        function editArticle(id) {
            window.location.href = '{{url('admin/editArticle?id=')}}' + id + '&page=' + '{{Request::get('page', 1)}}';
        }

        // 删除文章
        function delArticle(id) {
            layer.confirm('确定删除文章？', {icon: 2, title:'警告'}, function(index) {
                $.post("{{url('admin/delArticle')}}", {id:id, _token:'{{csrf_token()}}'}, function(ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.reload();
                        }
                    });
                });

                layer.close(index);
            });
        }
    </script>
@endsection