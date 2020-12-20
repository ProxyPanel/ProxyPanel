@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-block">
                        <h4 class="card-title cyan-600"><i class="icon wb-plus"></i>
                            {{trans('home.invite_code_make')}}
                        </h4>
                        <x-alert type="info" :message="trans('home.invite_code_tips', ['num'=>10, 'days' => sysConfig('user_invite_days')])"/>
                        @can('admin.invite.create')
                            <button type="button" class="btn btn-primary btn-animate btn-animate-side" onclick="makeInvite()">
                                <i class="icon wb-plus"></i> {{trans('home.invite_code_button')}}
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="panel">
                    <div class="panel-heading">
                        <h4 class="panel-title cyan-600">
                            <i class="icon wb-extension"></i>{{trans('home.invite_code_my_codes')}}
                        </h4>
                        @can('admin.invite.export')
                            <div class="panel-actions">
                                <button class="btn btn-primary" onclick="exportInvite()">批量导出</button>
                            </div>
                        @endcan
                    </div>
                    <div class="panel-body">
                        <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                            <thead class="thead-default">
                            <tr>
                                <th> #</th>
                                <th> {{trans('home.invite_code_table_name')}} </th>
                                <th> {{trans('home.invite_code_table_date')}} </th>
                                <th> 生成者</th>
                                <th> {{trans('home.invite_code_table_status')}} </th>
                                <th> {{trans('home.invite_code_table_user')}} </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($inviteList as $invite)
                                <tr>
                                    <td> {{$invite->id}} </td>
                                    <td>
                                        <a href="javascript:void(0)" class="mt-clipboard" data-clipboard-action="copy"
                                           data-clipboard-text="{{route('register',['code' => $invite->code])}}">{{$invite->code}}</a>
                                    </td>
                                    <td> {{$invite->dateline}} </td>
                                    <td>
                                        {{$invite->inviter_id === null ? '系统生成' : ($invite->inviter->email ?? '【账号已删除】')}}
                                    </td>
                                    <td>
                                        {!!$invite->status_label!!}
                                    </td>
                                    <td>
                                        {{$invite->status === 1 ? ($invite->invitee->email ?? '【账号已删除】') : ''}}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-md-4">
                                {{trans('home.invite_code_summary', ['total' => $inviteList->total()])}}
                            </div>
                            <div class="col-md-8">
                                <nav class="Page navigation float-right">
                                    {{$inviteList->links()}}
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('javascript')
    <script src="/assets/custom/clipboardjs/clipboard.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script>
        @can('admin.invite.create')
        // 生成邀请码
        function makeInvite() {
          $.ajax({
            method: 'POST',
            url: '{{route('admin.invite.create')}}',
            async: false,
            data: {_token: '{{csrf_token()}}'},
            dataType: 'json',
            success: function(ret) {
              if (ret.status === 'success') {
                swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
              } else {
                swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
              }
            },
          });

          return false;
        }
        @endcan

        @can('admin.invite.export')
        // 导出邀请码
        function exportInvite() {
          swal.fire({
            title: '提示',
            text: '确定导出所有邀请码吗',
            icon: 'question',
            showCancelButton: true,
            cancelButtonText: '{{trans('home.ticket_close')}}',
            confirmButtonText: '{{trans('home.ticket_confirm')}}',
          }).then((result) => {
            if (result.value) {
              window.location.href = '{{route('admin.invite.export')}}';
            }
          });
        }
        @endcan

        const clipboard = new ClipboardJS('.mt-clipboard');
        clipboard.on('success', function() {
          swal.fire({
            title: '复制成功',
            icon: 'success',
            timer: 1300,
            showConfirmButton: false,
          });
        });
        clipboard.on('error', function() {
          swal.fire({
            title: '复制失败，请手动复制',
            icon: 'error',
            timer: 1500,
            showConfirmButton: false,
          });
        });
    </script>
@endsection
