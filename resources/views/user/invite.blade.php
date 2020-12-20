@extends('user.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-header">
        <h1 class="page-title cyan-600">
            <i class="icon wb-extension"></i>{{trans('home.invite_code')}}
        </h1>
    </div>
    <div class="page-content container-fluid">
        <x-alert type="info" :message="trans('home.promote_invite_code', ['traffic' => $referral_traffic, 'referral_percent' => $referral_percent * 100])"/>
        <div class="row">
            <div class="col-xxl-3 col-lg-4">
                <div class="card">
                    <div class="card-block">
                        <h4 class="card-title cyan-600">
                            <i class="icon wb-plus"></i> {{trans('home.invite_code_make')}}
                        </h4>
                        <x-alert type="info" :message="trans('home.invite_code_tips', ['num'=>$num, 'days' => sysConfig('user_invite_days')])"/>
                        <button type="button" class="btn btn-primary btn-animate btn-animate-side" onclick="makeInvite()" @if(!$num) disabled @endif>
                            <i class="icon wb-plus"></i> {{trans('home.invite_code_button')}}
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-xxl-9 col-lg-8">
                <div class="card">
                    <div class="card-block">
                        <h4 class="card-title cyan-600">
                            <i class="icon wb-extension"></i>{{trans('home.invite_code_my_codes')}}
                        </h4>
                        <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                            <thead class="thead-default">
                            <tr>
                                <th data-cell-style="cellStyle"> #</th>
                                <th> {{trans('home.invite_code_table_name')}} </th>
                                <th> {{trans('home.invite_code_table_date')}} </th>
                                <th> {{trans('home.invite_code_table_status')}} </th>
                                <th> {{trans('home.invite_code_table_user')}} </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($inviteList as $invite)
                                <tr>
                                    <td> {{$loop->iteration}} </td>
                                    <td>
                                        <a href="javascript:void(0)" class="mt-clipboard" data-clipboard-action="copy"
                                           data-clipboard-text="{{route('register', ['aff' => Auth::id(), 'code' => $invite->code])}}">{{$invite->code}}</a>
                                    </td>
                                    <td> {{$invite->dateline}} </td>
                                    <td>
                                        {!! $invite->status_label !!}
                                    </td>
                                    {{$invite->status === 1 ? ($invite->invitee->email ?? '【账号已删除】') : ''}}
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer card-footer-transparent">
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
      // 生成邀请码
      function makeInvite() {
        $.ajax({
          method: 'POST',
          async: false,
          url: '{{route('createInvite')}}',
          data: {_token: '{{csrf_token()}}'},
          dataType: 'json',
          success: function(ret) {
            if (ret.status === 'success') {
              swal.fire({title: ret.message, icon: 'success'}).then(() => window.location.reload());
            } else {
              swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
            }
          },
        });
        return false;
      }

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
