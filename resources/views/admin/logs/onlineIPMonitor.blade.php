@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">在线IP监控
                    <small>2分钟内的实时数据</small>
                </h3>
            </div>
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group col-lg-2 col-sm-2">
                        <input type="number" class="form-control" name="id" id="id" value="{{Request::input('id')}}"
                               placeholder="ID"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-5">
                        <input type="text" class="form-control" name="email" id="email"
                               value="{{Request::input('email')}}" placeholder="用户名"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-5">
                        <input type="text" class="form-control" name="ip" id="ip" value="{{Request::input('ip')}}"
                               placeholder="IP"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-3">
                        <input type="number" class="form-control" name="port" id="port" value="{{Request::input('port')}}"
                               placeholder="端口"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-5">
                        <select name="nodeId" id="nodeId" class="form-control" onChange="Search()">
                            <option value="" @if(Request::input('nodeId') == '') selected @endif hidden>选择节点</option>
                            @foreach($nodeList as $node)
                                <option value="{{$node->id}}"
                                        @if(Request::input('nodeId') == $node->id) selected @endif>{{$node->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4 btn-group">
                        <button class="btn btn-primary" onclick="Search()">搜 索</button>
                        <a href="{{route('admin.log.online')}}" class="btn btn-danger">重 置</a>
                    </div>
                </div>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 类型</th>
                        <th> 节点</th>
                        <th> 用户</th>
                        <th> IP</th>
                        <th> 归属地</th>
                        <th> 时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($list as $vo)
                        <tr>
                            <td>{{$vo->id}}</td>
                            <td>{{$vo->type}}</td>
                            <td>{{$vo->node ? $vo->node->name : '【节点已删除】'}}</td>
                            <td>{{$vo->user ? $vo->user->email : '【用户已删除】'}}</td>
                            <td>
                                @if (strpos($vo->ip, ',') !== false)
                                    @foreach (explode(',', $vo->ip) as $ip)
                                        <a href="https://www.ipip.net/ip/{{$ip}}.html" target="_blank">{{$ip}}</a>
                                    @endforeach
                                @else
                                    <a href="https://www.ipip.net/ip/{{$vo->ip}}.html" target="_blank">{{$vo->ip}}</a>
                                @endif
                            </td>
                            <td>{{strpos($vo->ip, ',') !== false? '':$vo->ipInfo}}</td>
                            <td>{{date('Y-m-d H:i:s',$vo->created_at)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$list->total()}}</code> 个账号
                    </div>
                    <div class="col-sm-8">
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
    <script>
      //回车检测
      $(document).on('keypress', 'input', function(e) {
        if (e.which === 13) {
          Search();
          return false;
        }
      });

      // 搜索
      function Search() {
        window.location.href = '{{route('admin.log.online')}}' + $('#id').val() + '?ip=' + $('#ip').val() + '&email=' +
            $('#email').val() + '&port=' + $('#port').val() + '&nodeId=' + $('#nodeId option:selected').val();
      }
    </script>
@endsection
