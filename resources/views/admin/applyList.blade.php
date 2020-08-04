@extends('admin.layouts')
@section('css')
    <link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">提现申请列表</h3>
            </div>
            <div class="panel-body">
                <div class="form-inline mb-20">
                    <div class="form-group">
                        <input type="text" class="form-control" name="username" value="{{Request::get('username')}}" id="username" placeholder="申请账号" autocomplete="off">
                        <select class="form-control" name="status" id="status">
                            <option value="" @if(Request::get('status') == '') selected hidden @endif>状态</option>
                            <option value="-1" @if(Request::get('status') == '-1') selected hidden @endif>驳回</option>
                            <option value="0" @if(Request::get('status') == '0') selected hidden @endif>待审核</option>
                            <option value="1" @if(Request::get('status') == '1') selected hidden @endif>审核通过待打款</option>
                            <option value="2" @if(Request::get('status') == '2') selected hidden @endif>已打款</option>
                        </select>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-primary" onclick="doSearch()">搜索</button>
                        <button class="btn btn-danger" onclick="doReset()">重置</button>
                    </div>
                </div>
                <table class="text-center" data-toggle="table" data-mobile-responsive="true">
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
                                        <button class="btn btn-sm btn-danger" onclick="doAudit('{{$apply->id}}')"> 审核</button>
                                    @else
                                        <button class="btn btn-sm btn-primary" onclick="doAudit('{{$apply->id}}')"><i class="icon wb-search"></i></button>
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
                        共 {{$applyList->total()}} 个申请
                    </div>
                    <div class="col-sm-8">
                        <div class="Page navigation float-right">
                            {{ $applyList->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script type="text/javascript">
        // 审核
        function doAudit(id) {
            window.open('/admin/applyDetail?id=' + id);
        }

        // 搜索
        function do_search() {
            const username = $("#username").val();
            const status = $("#status option:selected").val();

            window.location.href = '/admin/applyList?username=' + username + '&status=' + status;
        }

        // 重置
        function do_reset() {
            window.location.href = '/admin/applyList';
        }
    </script>
@endsection