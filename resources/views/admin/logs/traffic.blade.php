@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">流量日志</h2>
            </div>
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group col-lg-2 col-sm-4">
                        <input type="number" class="form-control" name="user_id" id="user_id" value="{{Request::input('user_id')}}" placeholder="用户ID"/>
                    </div>
                    <div class="form-group col-lg-3 col-sm-8">
                        <input type="text" class="form-control" name="email" id="email" value="{{Request::input('email')}}" placeholder="用户名"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4">
                        <input type="number" class="form-control" name="port" id="port" value="{{Request::input('port')}}" placeholder="用户端口"/>
                    </div>
                    <div class="form-group col-lg-3 col-sm-8">
                        <select class="form-control" name="nodeId" id="nodeId" onChange="Search()">
                            <option value="" @if(Request::input('nodeId') == '') selected @endif hidden>选择节点</option>
                            @foreach($nodeList as $node)
                                <option value="{{$node->id}}" @if(Request::input('nodeId') == $node->id) selected @endif>
                                    {{$node->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-lg-6 col-sm-12">
                        <div class="input-group input-daterange" data-plugin="datepicker">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="icon wb-calendar" aria-hidden="true"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" name="start" id="start" value="{{Request::input('startTime')}}" placeholder="{{date("Y-m-d")}}"/>
                            <div class="input-group-prepend">
                                <span class="input-group-text">至</span>
                            </div>
                            <input type="text" class="form-control" name="end" id="end" value="{{Request::input('endTime')}}"
                                   placeholder="{{date("Y-m-d",strtotime("+1 month"))}}"/>
                        </div>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4 btn-group">
                        <button class="btn btn-primary" onclick="Search()">搜 索</button>
                        <a href="{{route('admin.log.traffic')}}" class="btn btn-danger">重 置</a>
                    </div>
                </div>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 用户</th>
                        <th> 节点</th>
                        <th> 流量比例</th>
                        <th> 上传流量</th>
                        <th> 下载流量</th>
                        <th> 总流量</th>
                        <th> 记录时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($list as $vo)
                        <tr>
                            <td> {{$vo->id}} </td>
                            <td>
                                @if(empty($vo->user))
                                    【账号已删除】
                                @else
                                    @can('admin.user.index')
                                        <a href="{{route('admin.user.index', ['id' => $vo->user->id])}}" target="_blank"> {{$vo->user->email}} </a>
                                    @else
                                        {{$vo->user->email}}
                                    @endcan
                                @endif
                            </td>
                            <td> {{$vo->node->name ?? '【节点已删除】'}} </td>
                            <td> {{$vo->rate}} </td>
                            <td> {{$vo->u}} </td>
                            <td> {{$vo->d}} </td>
                            <td><span class="badge badge-danger"> {{$vo->traffic}} </span></td>
                            <td> {{$vo->log_time}} </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-6">
                        共 <code>{{$list->total()}} 条记录</code>，合计 <code>{{$totalTraffic}}</code>
                    </div>
                    <div class="col-sm-6">
                        <nav class="Page navigation float-right">
                            {{$list->links()}}
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
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script>
      $('.input-daterange').datepicker({
        format: 'yyyy-mm-dd',
      });
      //回车检测
      $(document).on('keypress', 'input', function(e) {
        if (e.which === 13) {
          Search();
          return false;
        }
      });

      // 搜索
      function Search() {
        window.location.href = '{{route('admin.log.traffic')}}?port=' + $('#port').val() + '&user_id=' + $('#user_id').val() + '&email=' + $('#email').val()
            + '&nodeId=' + $('#nodeId option:selected').val() + '&startTime=' + $('#start').val() + '&endTime=' + $('#end').val();
      }
    </script>
@endsection
