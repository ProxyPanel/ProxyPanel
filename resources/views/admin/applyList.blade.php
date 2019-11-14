@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">提现申请列表</h3>
            </div>
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group col-lg-2 col-sm-4">
                        <input type="text" class="form-control" name="username" value="{{Request::get('username')}}" id="username" placeholder="申请账号"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4">
                        <select class="form-control" name="status" id="status" onChange="Search()">
                            <option value="" @if(Request::get('status') == '') selected hidden @endif>状态</option>
                            <option value="-1" @if(Request::get('status') == '-1') selected hidden @endif>驳回</option>
                            <option value="0" @if(Request::get('status') == '0') selected hidden @endif>待审核</option>
                            <option value="1" @if(Request::get('status') == '1') selected hidden @endif>审核通过待打款</option>
                            <option value="2" @if(Request::get('status') == '2') selected hidden @endif>已打款</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-1 col-sm-4 btn-group">
                        <button class="btn btn-primary" onclick="Search()">搜索</button>
                        <a href="/admin/applyList" class="btn btn-danger">重置</a>
                    </div>
                </div>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 申请时间</th>
                        <th> 申请账号</th>
                        <th> 申请提现金额</th>
                        <th> 状态</th>
                        <th> 处理时间</th>
                        <th> 操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($applyList->isEmpty())
                        <tr>
                            <td colspan="7">暂无数据</td>
                        </tr>
                    @else
                        @foreach($applyList as $apply)
                            <tr>
                                <td> {{$apply->id}} </td>
                                <td> {{$apply->created_at}} </td>
                                <td>
                                    @if(empty($apply->user))
                                        【账号已删除】
                                    @else
                                        <a href="/admin/userList?id={{$apply->user_id}}" target="_blank">{{$apply->user->username}}</a>
                                    @endif
                                </td>
                                <td> ￥{{$apply->amount}} </td>
                                <td>
                                    @if($apply->status == -1)
                                        <span class="badge badge-default badge-danger"> 驳回 </span>
                                    @elseif($apply->status == 0)
                                        <span class="badge badge-default badge-info"> 待审核 </span>
                                    @elseif($apply->status == 2)
                                        <span class="badge badge-default badge-success"> 已打款 </span>
                                    @else
                                        <span class="badge badge-default badge-default"> 审核通过待打款 </span>
                                    @endif
                                </td>
                                <td> {{$apply->created_at == $apply->updated_at ? '' : $apply->updated_at}} </td>
                                <td>
                                    @if($apply->status > 0 && $apply->status < 2)
                                        <a href="/admin/applyDetail?id={{$apply->id}}" class="btn btn-sm btn-danger">审核</a>
                                    @else
                                        <a href="/admin/applyDetail?id={{$apply->id}}" class="btn btn-sm btn-primary"><i class="icon wb-search"></i></a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$applyList->total()}}</code> 个申请
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
@section('script')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        //回车检测
        $(document).on("keypress", "input", function (e) {
            if (e.which === 13) {
                Search()
            }
        });

        // 搜索
        function Search() {
            const username = $("#username").val();
            const status = $("#status option:selected").val();
            window.location.href = '/admin/applyList?username=' + username + '&status=' + status;
        }
    </script>
@endsection
