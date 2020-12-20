@extends('user.layouts')
@section('css')
    <link href="/assets/global/fonts/themify/themify.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-lg-8 order-lg-1 order-2">
                <div class="panel panel-bordered">
                    <div class="panel-heading p-20">
                        <h1 class="panel-title cyan-600">
                            <i class="icon wb-user-circle"></i>{{trans('home.ticket_title')}}
                        </h1>
                        <div class="panel-actions">
                            <button class="btn btn-primary btn-animate btn-animate-side" data-toggle="modal" data-target="#add_ticket_modal">
                                <span>
                                    <i class="icon wb-plus" aria-hidden="true"></i> {{trans('home.ticket_table_new_button')}}
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-hover text-center">
                                <thead class="thead-default">
                                <tr>
                                    <th data-cell-style="cellStyle"> #</th>
                                    <th> {{trans('home.ticket_table_title')}} </th>
                                    <th> {{trans('home.ticket_table_status')}} </th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($tickets as $ticket)
                                    <tr>
                                        <td>{{$ticket->id}}</td>
                                        <td>{{$ticket->title}}</td>
                                        <td>{!!$ticket->status_label!!}</td>
                                        <td>
                                            <a href="{{route('replyTicket', ['id' => $ticket->id])}}" class="btn btn-animate btn-animate-vertical btn-outline-info">
                                                <span>
                                                    @if($ticket->status === 2)
                                                        <i class="icon wb-eye" aria-hidden="true" style="left: 40%"> </i>{{trans('home.ticket_table_view')}}
                                                    @else
                                                        <i class="icon wb-check" aria-hidden="true" style="left: 40%"> </i>{{trans('home.ticket_open')}}
                                                    @endif
                                                </span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-md-12">
                                <nav class="Page navigation float-right">
                                    {{$tickets->links()}}
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 order-lg-2 order-1">
                <div class="panel panel-bordered">
                    <div class="panel-heading p-20">
                        <h3 class="panel-title cyan-600"><i class="icon ti-headphone-alt"></i>客服工作时间</h3>
                    </div>
                    <div class="panel-body pt-0">
                        <ul class="list-group list-group-dividered list-group-full vertical-align-middle">
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-lg-5 col-4">
                                        <button class="btn btn-pure ti-time blue-700"></button>
                                        在线时间
                                    </div>
                                    <div class="col-lg-7 col-8 text-right">
                                        周一至周五 23:00 - 次日 11:00
                                        <br>
                                        周末 21:00 - 次日 12:00
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-2">
                                        <button class="btn btn-pure ti-info-alt red-700"></button>
                                    </div>
                                    <div class="col-10">
                                        本站有多种联系方式，请使用其中<code>一种</code>联系客服！
                                        <br>重复请求，将会自动延迟处理时间
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="add_ticket_modal" class="modal fade" tabindex="-1" data-focus-on="input:first" data-keyboard="false">
        <div class="modal-dialog modal-simple modal-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title"> {{trans('home.ticket_table_new_button')}} </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xl-12 form-group">
                            <input type="text" class="form-control" name="title" id="title" placeholder="{{trans('home.ticket_table_new_title')}}">
                        </div>
                        <div class="col-xl-12 form-group">
                            <textarea class="form-control" rows="5" name="content" id="content" placeholder="{{trans('home.ticket_table_content')}}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-danger"> {{trans('home.ticket_cancel')}} </button>
                    <button type="button" data-dismiss="modal" class="btn btn-success" onclick="createTicket()"> {{trans('home.ticket_confirm')}} </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script>
      // 发起工单
      function createTicket() {
        const title = $('#title').val();
        const content = $('#content').val();

        if (title.trim() === '') {
          swal.fire({title: '您未填写工单标题!', icon: 'warning'});
          return false;
        }

        if (content.trim() === '') {
          swal.fire({title: '您未填写工单内容!', icon: 'warning'});
          return false;
        }

        swal.fire({
          title: '确定提交工单？',
          icon: 'question',
          showCancelButton: true,
          cancelButtonText: '{{trans('home.ticket_close')}}',
          confirmButtonText: '{{trans('home.ticket_confirm')}}',
        }).then((result) => {
          if (result.value) {
            $.post('{{route('openTicket')}}', {
              _token: '{{csrf_token()}}',
              title: title,
              content: content,
            }, function(ret) {
              if (ret.status === 'success') {
                swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
              } else {
                swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
              }
            });
          }
        });
      }
    </script>
@endsection
