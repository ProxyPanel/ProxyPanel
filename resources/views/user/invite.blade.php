@extends('user.layouts')

@section('css')
@endsection
@section('title', trans('home.panel'))
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-4">
                <div class="tab-pane active">
                    <div class="portlet light bordered">
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject font-dark bold">{{trans('home.invite_code_make')}}</span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="alert alert-info">
                                <i class="fa fa-warning"></i>
                                {{trans('home.invite_code_tips1')}} <strong> {{$num}} </strong> {{trans('home.invite_code_tips2')}}
                            </div>
                            <button type="button" class="btn blue" onclick="makeInvite()" @if(!$num) disabled @endif> {{trans('home.invite_code_button')}} </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="tab-pane active">
                    <div class="portlet light bordered">
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject font-dark bold">{{trans('home.invite_code_my_codes')}}</span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="table-scrollable table-scrollable-borderless">
                                <table class="table table-hover table-light table-checkable order-column">
                                    <thead>
                                        <tr>
                                            <th> # </th>
                                            <th> {{trans('home.invite_code_table_name')}} </th>
                                            <th> {{trans('home.invite_code_table_date')}} </th>
                                            <th> {{trans('home.invite_code_table_status')}} </th>
                                            <th> {{trans('home.invite_code_table_user')}} </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($inviteList->isEmpty())
                                            <tr>
                                                <td colspan="5" style="text-align: center;">{{trans('home.invite_code_table_none_codes')}}</td>
                                            </tr>
                                        @else
                                            @foreach($inviteList as $key => $invite)
                                                <tr>
                                                    <td> {{$key + 1}} </td>
                                                    <td> <a href="{{url('register?aff='.Session::get('user')['id'].'&code='.$invite->code)}}" target="_blank">{{$invite->code}}</a> </td>
                                                    <td> {{$invite->dateline}} </td>
                                                    <td>
                                                        @if($invite->status == '0')
                                                            <span class="label label-sm label-success"> {{trans('home.invite_code_table_status_un')}} </span>
                                                        @elseif($invite->status == '1')
                                                            <span class="label label-sm label-danger"> {{trans('home.invite_code_table_status_yes')}} </span>
                                                        @else
                                                            <span class="label label-sm label-default"> {{trans('home.invite_code_table_status_expire')}} </span>
                                                        @endif
                                                    </td>
                                                    <td> {{empty($invite->user) ? '' : $invite->user->username}} </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4">
                                    <div class="dataTables_info" role="status" aria-live="polite">共 {{$inviteList->total()}} 个邀请码</div>
                                </div>
                                <div class="col-md-8 col-sm-8">
                                    <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                        {{ $inviteList->links() }}
                                    </div>
                                </div>
                            </div>
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
    <script src="/js/layer/layer.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 生成邀请码
        function makeInvite() {
            var _token = '{{csrf_token()}}';

            $.ajax({
                type: "POST",
                url: "{{url('user/makeInvite')}}",
                async: false,
                data: {_token:_token},
                dataType: 'json',
                success: function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.reload();
                        }
                    });
                }
            });

            return false;
        }
    </script>
@endsection