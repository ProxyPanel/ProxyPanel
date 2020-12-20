@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">线路Ping测速日志</h2>
            </div>
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group col-lg-2 col-sm-5">
                        <select name="nodeId" id="nodeId" class="form-control" onChange="Search()">
                            <option value="" @if(Request::input('nodeId') === '') selected @endif hidden>选择节点</option>
                            @foreach($nodeList as $node)
                                <option value="{{$node->id}}" @if((int) Request::input('nodeId') === $node->id) selected @endif>
                                    {{$node->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-lg-1 col-sm-4 btn-group">
                        <button class="btn btn-primary" onclick="Search()">搜 索</button>
                        <a href="{{route('admin.node.pingLog')}}" class="btn btn-danger">重 置</a>
                    </div>
                </div>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th rowspan="2"> #</th>
                        <th rowspan="2"> 节点</th>
                        <th colspan="4"> 速度</th>
                    </tr>
                    <tr>
                        <th>电信</th>
                        <th>联通</th>
                        <th>移动</th>
                        <th>香港</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($pingLogs as $log)
                        <tr>
                            <td> {{$log->id}} </td>
                            <td> {{$log->node->name}} </td>
                            <td> {{$log->ct? $log->ct.' ms': '无'}} </td>
                            <td> {{$log->cu? $log->cu.' ms': '无'}} </td>
                            <td> {{$log->cm? $log->cm.' ms': '无'}} </td>
                            <td> {{$log->hk? $log->hk.' ms': '无'}} </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$pingLogs->total()}}</code> 条记录
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$pingLogs->links()}}
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
      // 搜索
      function Search() {
        window.location.href = '{{route('admin.node.pingLog')}}?&nodeId=' + $('#nodeId option:selected').val();
      }
    </script>
@endsection
