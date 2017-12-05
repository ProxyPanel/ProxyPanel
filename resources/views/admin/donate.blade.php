@extends('admin.layouts')

@section('css')
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-body">
                        <div class="row">
                            <p>本程序免费、开源、无版权，要是觉得还不错，请打赏一下支持作者持续开发</p>
                            <p>感恩的心，感谢有你</p>
                            <p>Telegram频道：<a href="" target="_blank">https://t.me/ssrpanel</a></p>
                            <p>Telegram群组：<a href="" target="_blank">https://t.me/chatssrpanel</a></p>
                            <a href="https://github.com/ssrpanel/ssrpanel" target="_blank"><img src="{{asset('assets/images/donate.jpeg')}}" /></a>
                        </div>
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
        //
    </script>
@endsection