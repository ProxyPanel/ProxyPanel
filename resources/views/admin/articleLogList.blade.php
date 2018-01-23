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
                            <span class="caption-subject bold uppercase"> 文章访问日志 </span>
                        </div>
                        <div class="actions">
                            <div class="btn-group">

                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column">
                                <thead>
                                <tr>
                                    <th> # </th>
                                    <th> 文章ID </th>
                                    <th> 坐标 </th>
                                    <th> IP </th>
                                    <th> 头部信息 </th>
                                    <th> 状态 </th>
                                    <th> 访问时间 </th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($articleLogList->isEmpty())
                                    <tr>
                                        <td colspan="7">暂无数据</td>
                                    </tr>
                                @else
                                    @foreach($articleLogList as $articleLog)
                                        <tr class="odd gradeX">
                                            <td> {{$articleLog->id}} </td>
                                            <td> <a href="{{url('article?id=' . $articleLog->aid)}}" target="_blank"> {{$articleLog->aid}} </a> </td>
                                            <td> {{$articleLog->lat}},{{$articleLog->lng}} </td>
                                            <td> {{$articleLog->ip}} </td>
                                            <td> {{$articleLog->headers}} </td>
                                            <td>
                                                @if ($articleLog->status)
                                                    <span class="label label-default">已查看</span>
                                                @else
                                                    <span class="label label-info">未查看</span>
                                                @endif
                                            </td>
                                            <td> {{$articleLog->created_at}} </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$articleLogList->total()}} 条日志</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $articleLogList->links() }}
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
        //
    </script>
@endsection