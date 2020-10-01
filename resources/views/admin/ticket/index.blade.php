@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">工单列表</h3>
                <div class="panel-actions">
                    <button class="btn btn-primary btn-animate btn-animate-side" data-toggle="modal" data-target="#add_ticket_modal">
                        <span>
                            <i class="icon wb-plus" aria-hidden="true"></i> {{trans('home.ticket_table_new_button')}}
                        </span>
                    </button>
                </div>
            </div>
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group col-lg-3 col-sm-6">
                        <input type="text" class="form-control" name="email" id="email" value="{{Request::input('email')}}" placeholder="用户名" autocomplete="off"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button class="btn btn-primary" onclick="Search()">搜 索</button>
                        <a href="{{route('admin.ticket.index')}}" class="btn btn-danger">重 置</a>
                    </div>
                </div>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 用户名</th>
                        <th> 标题</th>
                        <th> 状态</th>
                        <th> 操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($ticketList as $ticket)
                        <tr>
                            <td> {{$ticket->id}} </td>
                            <td>
                                @if(!$ticket->user)
                                    【账号已删除】
                                @else
                                    <a href="{{route('admin.user.index', ['id'=>$ticket->user->id])}}" target="_blank">{{$ticket->user->email}}</a>
                                @endif
                            </td>

                            <td>
                                {{$ticket->title}}
                            </td>
                            <td>
                                {!!$ticket->status_label!!}
                            </td>
                            <td>
                                <a href="{{route('admin.ticket.edit',$ticket->id)}}" class="btn btn-animate btn-animate-vertical btn-outline-info">
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
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$ticketList->total()}}</code> 个工单
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

    <div id="add_ticket_modal" class="modal fade" tabindex="-1" data-focus-on="input:first" data-keyboard="false">
        <div class="modal-dialog modal-simple modal-center modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title"> {{trans('home.ticket_table_new_button')}} </h4>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="domain" class="col-2 col-form-label">域名</label>
                        <div class="input-group col-10">
                            <input type="number" class="form-control col-md-4" name="user_id" id="user_id" placeholder="用户ID"/>
                            <div class="input-group-prepend">
                                <span class="input-group-text">或</span>
                            </div>
                            <input type="email" class="form-control col-md-8" name="user_email" id="user_email" placeholder="用户邮箱"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="title" id="title" placeholder="标题">
                    </div>
                    <div class="form-group">
                        <textarea type="text" class="form-control" rows="5" name="content" id="content" placeholder="内容"></textarea>
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
@section('script')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        //回车检测
        $(document).on('keypress', 'input', function(e) {
            if (e.which === 13) {
                Search();
                return false;
            }
        });

        // 搜索
        function Search() {
            window.location.href = '{{route('admin.ticket.index')}}?email=' + $('#email').val();
        }

        // 发起工单
        function createTicket() {
            const id = $('#user_id').val();
            const email = $('#user_email').val();
            const title = $('#title').val();
            const content = $('#content').val();

            if (id.trim() === '' && email.trim() === '') {
                swal.fire({title: '请填入目标用户信息!', type: 'warning'});
                return false;
            }

            if (title.trim() === '') {
                swal.fire({title: '您未填写工单标题!', type: 'warning'});
                return false;
            }

            if (content.trim() === '') {
                swal.fire({title: '您未填写工单内容!', type: 'warning'});
                return false;
            }

            swal.fire({
                title: '确定提交工单？',
                type: 'question',
                showCancelButton: true,
                cancelButtonText: '{{trans('home.ticket_close')}}',
                confirmButtonText: '{{trans('home.ticket_confirm')}}',
            }).then((result) => {
                if (result.value) {
                    $.post('{{route('admin.ticket.store')}}', {
                        _token: '{{csrf_token()}}',
                        id: id,
                        email: email,
                        title: title,
                        content: content,
                    }, function(ret) {
                        if (ret.status === 'success') {
                            swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false}).
                                then(() => window.location.reload());
                        }
                        else {
                            swal.fire({title: ret.message, type: 'error'}).then(() => window.location.reload());
                        }
                    });
                }
            });
        }
    </script>
@endsection
