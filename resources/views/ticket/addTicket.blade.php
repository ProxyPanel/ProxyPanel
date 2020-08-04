@extends('admin.layouts')
@section('content')
    <div class="page-content container">
        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
                {{$errors->first()}}
            </div>
        @endif
        <div class="panel">
            <div class="panel-heading p-20">
                <h1 class="panel-title cyan-600"><i class="icon wb-add"></i>发起工单</h1>
            </div>
            <div class="panel-body">
                <form action="/ticket/addTicket" method="post" enctype="multipart/form-data" class="form-horizontal" onsubmit="return do_submit();">
                    <div class="form-body">
                        <div class="form-group">
                            <label class="control-label col-md-2">用户名</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="username" value="{{Request::get('username')}}" id="username" autocomplete="off" autofocus required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2">标题</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="title" id="title" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-2">内容</label>
                            <div class="col-md-8">
                                <textarea class="form-control" rows="5" name="content" id="content" placeholder="{{trans('home.ticket_table_content')}}"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-2 col-md-10">
                                <button type="submit" class="btn btn-success">提 交</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
        // ajax同步提交
        function do_submit() {
            const username = $('#username').val();
            const title = $('#title').val();
            const content = $('#content').val();

            $.ajax({
                type: "POST",
                url: "/ticket/addTicket",
                async: false,
                data: {_token: '{{csrf_token()}}', username: username, title: title, content: content},
                dataType: 'json',
                success: function (ret) {
                    swal.fire({title: ret.message, type: 'success', timer: 1000})
                        .then(() => window.location.href = '/ticket/ticketList');
                }
            });

            return false;
        }
    </script>
@endsection
