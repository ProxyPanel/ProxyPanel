@extends('admin.layouts')
@section('css')
@endsection
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-dark sbold uppercase">添加敏感词</span>
                        </div>
                        <div class="actions"></div>
                    </div>
                    <div class="portlet-body form">
                        @if (Session::has('errorMsg'))
                            <div class="alert alert-danger">
                                <button class="close" data-close="alert"></button>
                                <strong>错误：</strong> {{Session::get('errorMsg')}}
                            </div>
                        @endif
                        <!-- BEGIN FORM-->
                        <form action="#" method="post" enctype="multipart/form-data" class="form-horizontal" role="form" onsubmit="return do_submit();">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="control-label col-md-3">敏感词</label>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="words" value="" id="words" placeholder="" required>
                                        <input type="hidden" name="_token" value="{{csrf_token()}}" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-4">
                                        <button type="submit" class="btn green">提交</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- END FORM-->
                    </div>
                </div>
                <!-- END PORTLET-->
            </div>
        </div>
        <div id="charge_modal" class="modal fade" tabindex="-1" data-focus-on="input:first" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title"> {{trans('home.ticket_table_new_button')}} </h4>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="title" id="title" placeholder="{{trans('home.ticket_table_title')}}" class="form-control margin-bottom-20">
                        <textarea name="content" id="content" placeholder="{{trans('home.ticket_table_new_desc')}}" class="form-control margin-bottom-20" rows="4"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-dismiss="modal" class="btn dark btn-outline"> {{trans('home.ticket_table_new_cancel')}} </button>
                        <button type="button" data-dismiss="modal" class="btn green btn-outline" onclick="addTicket()"> {{trans('home.ticket_table_new_yes')}} </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script type="text/javascript">
        // ajax同步提交
        function do_submit() {
            var _token = '{{csrf_token()}}';
            var words = $('#words').val();

            $.ajax({
                type: "POST",
                url: "{{url('admin/addSensitiveWords')}}",
                async: false,
                data: {_token:_token, words:words},
                dataType: 'json',
                success: function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.href = '{{url('admin/sensitiveWordsList')}}';
                        }
                    });
                }
            });

            return false;
        }
    </script>
@endsection