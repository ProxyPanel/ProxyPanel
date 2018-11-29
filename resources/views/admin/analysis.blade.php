@extends('admin.layouts')
@section('css')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
@endsection
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
                            <span class="caption-subject bold uppercase"> 日志分析 </span><small>仅适用于单机单节点</small>
                        </div>
                    </div>
                    <div class="portlet-body">
                        @if (Session::has('analysisErrorMsg'))
                            <div class="alert alert-danger">
                                <button class="close" data-close="alert"></button>
                                <i class="fa fa-warning"></i>
                                {{Session::get('analysisErrorMsg')}}
                            </div>
                        @else
                            <table class="table table-striped table-bordered table-hover order-column" id="analysis">
                                <thead>
                                    <tr>
                                        <th> 近期请求地址 </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(empty($urlList))
                                        <tr>
                                            <td colspan="2">访问记录不足15000条，无法分析数据</td>
                                        </tr>
                                    @else
                                        @foreach($urlList as $url)
                                            <tr class="odd gradeX">
                                                <td> {{$url}} </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        @endif
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
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>

    <script type="text/javascript">
        var TableDatatablesScroller = function(){var e=function(){
            var e = $("#analysis");
            e.dataTable({
                language:{aria:{
                    sortAscending:": activate to sort column ascending",
                    sortDescending:": activate to sort column descending"},
                    emptyTable:"暂无数据",
                    info:"第 _START_ 到 _END_ 条，共计 _TOTAL_ 条",
                    infoEmpty:"未找到",
                    infoFiltered:"(filtered1 from _MAX_ total entries)",
                    lengthMenu:"_MENU_ entries",
                    search:"搜索:",
                    zeroRecords:"未找到"},
                buttons:[
                    {extend:"print",className:"btn dark btn-outline"},
                    {extend:"pdf",className:"btn green btn-outline"},
                    {extend:"csv",className:"btn purple btn-outline "}
                ],
                scrollY:300,
                deferRender:!0,
                scroller:!0,
                stateSave:!0,
                order:[[0,"asc"]],
                lengthMenu:[[10,15,20,-1],[10,15,20,"All"]],
                pageLength:20,
                dom:"<'row' <'col-md-12'B>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>"
        })};
        return{init:function(){jQuery().dataTable&&(e())}}}();
            jQuery(document).ready(function(){TableDatatablesScroller.init()});

        $('#is_rand_port').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_rand_port = 0;

                if (state) {
                    is_rand_port = 1;
                }

                $.post("{{url('admin/enableRandPort')}}", {_token:'{{csrf_token()}}', value:is_rand_port}, function (ret) {
                    console.log(ret);
                });
            }
        });
    </script>
@endsection