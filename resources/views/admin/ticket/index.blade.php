@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('admin.ticket.title') }}</h3>
                @can('admin.ticket.store')
                    <div class="panel-actions">
                        <button class="btn btn-primary btn-animate btn-animate-side" data-toggle="modal" data-target="#add_ticket_modal">
                        <span>
                            <i class="icon wb-plus" aria-hidden="true"></i> {{trans('user.ticket.new')}}
                        </span>
                        </button>
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-3 col-sm-6">
                        <input type="text" class="form-control" name="username" value="{{Request::query('username')}}" placeholder="{{ trans('model.user.username') }}"
                               autocomplete="off"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button type="submit" class="btn btn-primary">{{ trans('common.search') }}</button>
                        <a href="{{route('admin.ticket.index')}}" class="btn btn-danger">{{ trans('common.reset') }}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> {{ trans('model.user.username') }}</th>
                        <th> {{ trans('validation.attributes.title') }}</th>
                        <th> {{ trans('common.status.attribute') }}</th>
                        <th> {{ trans('common.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($ticketList as $ticket)
                        <tr>
                            <td> {{$ticket->id}} </td>
                            <td>
                                @if(!$ticket->user)
                                    【{{trans('common.deleted_item', ['attribute' => trans('common.account')])}}】
                                @else
                                    @can('admin.user.index')
                                        <a href="{{route('admin.user.index', ['id' => $ticket->user->id])}}" target="_blank">{{$ticket->user->username}}</a>
                                    @else
                                        {{$ticket->user->username}}
                                    @endcan
                                @endif
                            </td>

                            <td>
                                {{$ticket->title}}
                            </td>
                            <td>
                                {!!$ticket->status_label!!}
                            </td>
                            <td>
                                @can('admin.ticket.edit')
                                    <a href="{{route('admin.ticket.edit', $ticket)}}" class="btn btn-animate btn-animate-vertical btn-outline-info">
                                        <span>
                                            @if($ticket->status === 2)
                                                <i class="icon wb-eye" aria-hidden="true" style="left: 40%"> </i>{{trans('common.view')}}
                                            @else
                                                <i class="icon wb-check" aria-hidden="true" style="left: 40%"> </i>{{trans('common.open')}}
                                            @endif
                                        </span>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.ticket.counts', ['num' => $ticketList->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$ticketList->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('admin.ticket.store')
        <div id="add_ticket_modal" class="modal fade" tabindex="-1" data-focus-on="input:first" data-keyboard="false">
            <div class="modal-dialog modal-simple modal-center modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('common.close') }}">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title"> {{trans('user.ticket.new')}} </h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="userId" class="col-2 col-form-label">{{ trans('model.user.attribute') }}</label>
                            <div class="input-group col-10">
                                <input type="number" class="form-control col-md-4" name="uid" id="uid" placeholder="{{ trans('model.user.id') }}"/>
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ trans('common.or') }}</span>
                                </div>
                                <input type="text" class="form-control col-md-8" name="username" id="username" placeholder="{{ trans('model.user.username') }}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="title" id="title" placeholder="{{ trans('validation.attributes.title') }}">
                        </div>
                        <div class="form-group">
                            <textarea type="text" class="form-control" rows="5" name="content" id="content" placeholder="{{ trans('validation.attributes.content') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-dismiss="modal" class="btn btn-danger mr-auto"> {{ trans('common.cancel') }} </button>
                        <button type="button" data-dismiss="modal" class="btn btn-success" onclick="createTicket()"> {{ trans('common.confirm') }} </button>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script>
        @can('admin.ticket.store')
        // 发起工单
        function createTicket() {
          const uid = $('#uid').val();
          const username = $('#username').val();
          const title = $('#title').val();
          const content = $('#content').val();

          if (uid.trim() === '' && username.trim() === '') {
            swal.fire({title: '{{ trans('admin.ticket.send_to') }}', icon: 'warning'});
            return false;
          }

          if (title.trim() === '') {
            swal.fire({
              title: '{{ trans('validation.required', ['attribute' => trans('validation.attributes.title')]) }}',
              icon: 'warning',
            });
            return false;
          }

          if (content.trim() === '') {
            swal.fire({
              title: '{{ trans('validation.required', ['attribute' => trans('validation.attributes.content')]) }}',
              icon: 'warning',
            });
            return false;
          }

          swal.fire({
            title: '{{ trans('user.ticket.submit_tips') }}',
            icon: 'question',
            showCancelButton: true,
            cancelButtonText: '{{ trans('common.close') }}',
            confirmButtonText: '{{ trans('common.confirm') }}',
          }).then((result) => {
            if (result.value) {
              $.ajax({
                method: 'POST',
                url: "{{route('admin.ticket.store')}}",
                data: {
                  _token: '{{csrf_token()}}',
                  uid: uid,
                  username: username,
                  title: title,
                  content: content,
                },
                dataType: 'json',
                success: function(ret) {
                  $('#add_ticket_modal').modal('hide');
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
                  $('#add_ticket_modal').modal('hide');
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
          });
        }
        @endcan
    </script>
@endsection
