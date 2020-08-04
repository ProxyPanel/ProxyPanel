@extends('admin.layouts')
@section('css')
    <link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">订阅设备列表</h3>
            </div>
            <div class="panel-body">
                <div class="form-inline pb-20">
                    <div class="form-group">
                        <select name="status" id="status" class="form-control">
                            <option value="" @if(Request::get('status') == '') selected hidden @endif>账号状态</option>
                            <option value="-1" @if(Request::get('status') == '-1') selected hidden @endif>禁用</option>
                            <option value="0" @if(Request::get('status') == '0') selected hidden @endif>未激活</option>
                            <option value="1" @if(Request::get('status') == '1') selected hidden @endif>正常</option>
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
                        <th> 名称</th>
                        <th> 类型</th>
                        <th> 平台</th>
                        <th> 请求头</th>
                        <th> 操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($deviceList->isEmpty())
                        <tr>
                            <td colspan="6">暂无数据</td>
                        </tr>
                    @else
                        @foreach($deviceList as $vo)
                            <tr>
                                <td> {{$vo->id}} </td>
                                <td> {{$vo->name}} </td>
                                <td> {!! $vo->type_label !!} </td>
                                <td> {!! $vo->platform_label !!} </td>
                                <td> {{$vo->header}} </td>
                                <td>
                                    @if($vo->status == 0)
                                        <button class="btn btn-sm btn-outline-success" onclick="setDeviceStatus('{{$vo->id}}', 1)">启用</button>
                                    @endif
                                    @if($vo->status == 1)
                                        <button class="btn btn-sm btn-outline-danger" onclick="setDeviceStatus('{{$vo->id}}', 0)">禁用</button>
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
                    <div class="col-md-4 col-sm-4">
						<p class="dataTables_info" role="status" aria-live="polite">共
							<code>{{$deviceList->total()}}</code> 条记录</p>
                    </div>
                    <div class="col-md-8 col-sm-8">
						<div class="float-right">
							<nav aria-label="Page navigation">{{ $deviceList->links() }}</nav>
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
        // 搜索
        function doSearch() {
			const status = $("#status option:selected").val();

			window.location.href = '/subscribe/deviceList' + '?status=' + status;
        }

        // 重置
        function doReset() {
            window.location.href = '/subscribe/deviceList';
        }

        // 启用禁用订阅设备
        function setDeviceStatus(id, status) {
            $.post("/subscribe/setDeviceStatus", {
                _token: '{{csrf_token()}}',
                id: id,
                status: status
            }, function (ret) {
                swal.fire({text: ret.message, timer: 1000, showConfirmButton: false,})
                    .then(() => {
                        window.location.reload();
                    })
            });
        }
    </script>
@endsection