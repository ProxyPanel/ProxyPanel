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
                            {{ trans('common.generate_item', ['attribute' => trans('user.invite.attribute')]) }}
                        </h4>
                        <x-alert type="info" :message="trans('user.invite.tips', ['num'=>10, 'days' => sysConfig('user_invite_days')])"/>
                        @can('admin.invite.create')
                            <button type="button" class="btn btn-primary btn-animate btn-animate-side" onclick="makeInvite()">
                                <i class="icon wb-plus"></i> {{ trans('common.generate') }}
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="panel">
                    <div class="panel-heading">
                        <h4 class="panel-title cyan-600">
                            <i class="icon wb-extension"></i>{{ trans('user.invite.attribute') }}
                        </h4>
                        @can('admin.invite.export')
                            <div class="panel-actions">
                                <button class="btn btn-primary" onclick="exportInvite()">{{ trans('admin.massive_export') }}</button>
                            </div>
                        @endcan
                    </div>
                    <div class="panel-body">
                        <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                            <thead class="thead-default">
                            <tr>
                                <th> #</th>
                                <th> {{ trans('user.invite.attribute') }} </th>
                                <th> {{ trans('common.available_date') }} </th>
                                <th> {{ trans('user.inviter') }}</th>
                                <th> {{ trans('common.status.attribute') }} </th>
                                <th> {{ trans('user.invitee') }} </th>
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
                                        {{$invite->inviter_id === null ? trans('admin.system_generate') : ($invite->inviter->username ?? '【'.trans('common.deleted_item', ['attribute' => trans('common.account')]).'】')}}
                                    </td>
                                    <td>
                                        {!!$invite->status_label!!}
                                    </td>
                                    <td>
                                        {{$invite->status === 1 ? ($invite->invitee->username ?? '【'.trans('common.deleted_item', ['attribute' => trans('common.account')]).'】') : ''}}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-md-4">
                                {!! trans('user.invite.counts', ['num' => $inviteList->total()]) !!}
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
            dataType: 'json',
            data: {_token: '{{csrf_token()}}'},
            success: function(ret) {
              if (ret.status === 'success') {
                swal.fire({
                  title: ret.message,
                  icon: 'success',
                  timer: 1000,
                  showConfirmButton: false,
                }).then(() => window.location.reload());
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
            title: '{{ trans('admin.hint') }}',
            text: '{{ trans('admin.confirm.export') }}',
            icon: 'question',
            showCancelButton: true,
            cancelButtonText: '{{ trans('common.close') }}',
            confirmButtonText: '{{ trans('common.confirm') }}',
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
            title: '{{ trans('common.copy.success') }}',
            icon: 'success',
            timer: 1300,
            showConfirmButton: false,
          });
        });
        clipboard.on('error', function() {
          swal.fire({
            title: '{{ trans('common.copy.failed') }}',
            icon: 'error',
            timer: 1500,
            showConfirmButton: false,
          });
        });
    </script>
@endsection
