@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('admin.aff.title') }}</h3>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-2 col-sm-4">
                        <input type="text" class="form-control" name="username" value="{{Request::query('username')}}" placeholder="{{ trans('model.user.username') }}"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4">
                        <select class="form-control" name="status" id="status" onchange="this.form.submit()">
                            <option value="" hidden>{{ trans('common.status.attribute') }}</option>
                            <option value="-1">{{ trans('common.status.rejected') }}</option>
                            <option value="0">{{ trans('common.status.review') }}</option>
                            <option value="1">{{ trans('common.status.reviewed') }}</option>
                            <option value="2">{{ trans('common.status.paid') }}</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-1 col-sm-4 btn-group">
                        <button type="submit" class="btn btn-primary">{{ trans('common.search') }}</button>
                        <a href="{{route('admin.aff.index')}}" class="btn btn-danger">{{ trans('common.reset') }}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> {{ trans('model.referral.created_at') }}</th>
                        <th> {{ trans('model.referral.user') }}</th>
                        <th> {{ trans('model.referral.amount') }}</th>
                        <th> {{ trans('common.status.attribute') }}</th>
                        <th> {{ trans('model.aff.updated_at') }}</th>
                        <th> {{ trans('common.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($applyList as $apply)
                        <tr>
                            <td> {{$apply->id}} </td>
                            <td> {{$apply->created_at}} </td>
                            <td>
                                @if(empty($apply->user))
                                    【{{trans('common.deleted_item', ['attribute' => trans('common.account')])}}】
                                @else
                                    @can('admin.user.index')
                                        <a href="{{route('admin.user.index', ['id'=>$apply->user_id])}}" target="_blank">
                                            {{$apply->user->username}}
                                        </a>
                                    @else
                                        {{$apply->user->username}}
                                    @endcan
                                @endif
                            </td>
                            <td> {{$apply->amount_tag}} </td>
                            <td>
                                @if($apply->status === -1)
                                    <span class="badge badge-lg badge-danger"> {{ trans('common.status.rejected') }} </span>
                                @elseif($apply->status === 0)
                                    <span class="badge badge-lg badge-info"> {{ trans('common.status.review') }} </span>
                                @elseif($apply->status === 2)
                                    <span class="badge badge-lg badge-success"> {{ trans('common.status.paid') }} </span>
                                @else
                                    <span class="badge badge-lg badge-default"> {{ trans('common.status.payment_pending') }} </span>
                                @endif
                            </td>
                            <td> {{$apply->created_at === $apply->updated_at ? '' : $apply->updated_at}} </td>
                            <td>
                                @canany(['admin.aff.setStatus', 'admin.aff.detail'])
                                    <div class="btn-group">
                                        @can('admin.aff.setStatus')
                                            @if($apply->status === 0)
                                                <a href="javascript:setStatus('{{$apply->id}}','1')" class="btn btn-sm btn-success">
                                                    <i class="icon wb-check" aria-hidden="true"></i>{{ trans('common.status.pass') }}
                                                </a>
                                                <a href="javascript:setStatus('{{$apply->id}}','-1')" class="btn btn-sm btn-danger">
                                                    <i class="icon wb-close" aria-hidden="true"></i>{{ trans('common.status.reject') }}
                                                </a>
                                            @elseif($apply->status === 1)
                                                @can('admin.user.updateCredit')
                                                    <a href="javascript:handleUserCredit('{{$apply->user->id}}','{{$apply->amount}}', '{{$apply->id}}','2')" class="btn
                                                    btn-sm
                                                    btn-success">
                                                        <i id="makePayment_{{$apply->id}}" class="icon wb-payment"
                                                           aria-hidden="true"></i> {{ trans('common.status.send_to_credit') }}
                                                    </a>
                                                @endcan
                                                <a href="javascript:setStatus('{{$apply->id}}', '2')" class="btn btn-sm btn-primary">
                                                    <i class="icon wb-check-circle" aria-hidden="true"></i> {{ trans('common.status.paid') }}
                                                </a>
                                            @endif
                                        @endcan
                                        @can('admin.aff.detail')
                                            <a href="{{route('admin.aff.detail', $apply->id)}}" class="btn btn-sm btn-default">
                                                <i class="icon wb-search"></i></a>
                                        @endcan
                                    </div>
                                @endcanany
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.aff.apply_counts', ['num' => $applyList->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$applyList->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script>
      $(document).ready(function() {
        $('#status').val({{Request::query('status')}});
      });

      @can('admin.aff.setStatus')
      // 更改状态
      function setStatus(id, status) {
        $.ajax({
          method: 'PUT',
          url: '{{route('admin.aff.setStatus','')}}/' + id,
          data: {
            _token: '{{csrf_token()}}',
            status: status,
          },
          dataType: 'json',
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
          error: function(data) {
            let str = '';
            const errors = data.responseJSON;
            if ($.isEmptyObject(errors) === false) {
              $.each(errors.errors, function(index, value) {
                str += '<li>' + value + '</li>';
              });
              swal.fire({
                title: '{{ trans('admin.hint') }}',
                html: str,
                icon: 'error',
                confirmButtonText: '{{ trans('common.confirm') }}',
              });
            }
          },
        });
      }
      @endcan

      @can('admin.user.updateCredit')
      // 余额充值
      function handleUserCredit(uid, amount, aid, status) {
        $.ajax({
          url: '{{route('admin.user.updateCredit', '')}}/' + uid,
          method: 'POST',
          data: {_token: '{{csrf_token()}}', amount: amount, description: '{{ trans('admin.aff.referral') }}'},
          beforeSend: function() {
            $('#makePayment_' + aid).removeClass('wb-payment').addClass('wb-loop icon-spin');
          },
          success: function(ret) {
            if (ret.status === 'success') {
              swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => {
                setStatus(aid, status);
              });
            } else {
              swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
            }
          },
          error: function() {
            $('#msg').show().html('{{ trans('common.request_failed') }}');
          },
          complete: function() {
          },
        });
      }
        @endcan
    </script>
@endsection
