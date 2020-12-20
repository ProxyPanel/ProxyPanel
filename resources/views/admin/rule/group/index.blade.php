@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">规则分组</h2>
                @can('admin.rule.group.create')
                    <div class="panel-actions">
                        <a href="{{route('admin.rule.group.create')}}" class="btn btn-outline-primary">
                            <i class="icon wb-plus" aria-hidden="true"></i>添加分组
                        </a>
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 分组名称</th>
                        <th> 审计模式</th>
                        <th> 操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($ruleGroupList as $ruleGroup)
                        <tr>
                            <td> {{$ruleGroup->id}} </td>
                            <td> {{$ruleGroup->name}} </td>
                            <td> {!! $ruleGroup->type_label !!} </td>
                            <td>
                                @canany(['admin.rule.group.editNode', 'admin.rule.group.edit', 'admin.rule.group.destroy'])
                                    <div class="btn-group">
                                        @can('admin.rule.group.editNode')
                                            <a href="{{route('admin.rule.group.editNode', $ruleGroup->id)}}" class="btn btn-sm btn-outline-primary">
                                                <i class="icon wb-plus" aria-hidden="true"></i>分配节点
                                            </a>
                                        @endcan
                                        @can('admin.rule.group.edit')
                                            <a href="{{route('admin.rule.group.edit', $ruleGroup->id)}}" class="btn btn-sm btn-outline-primary">
                                                <i class="icon wb-edit"></i>编辑
                                            </a>
                                        @endcan
                                        @can('admin.rule.group.destroy')
                                            <button onclick="delRuleGroup('{{route('admin.rule.group.destroy', $ruleGroup->id)}}', '{{$ruleGroup->name}}')"
                                                    class="btn btn-sm btn-outline-danger">
                                                <i class="icon wb-trash"></i>删除
                                            </button>
                                        @endcan
                                    </div>
                                @endcanany
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$ruleGroupList->total()}}</code> 个分组
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$ruleGroupList->links()}}
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
    @can('admin.rule.group.destroy')
        <script>
          // 删除规则分组
          function delRuleGroup(url, name) {
            swal.fire({
              title: '警告',
              text: '确定删除分组 【' + name + '】 ？',
              icon: 'warning',
              showCancelButton: true,
              cancelButtonText: '{{trans('home.ticket_close')}}',
              confirmButtonText: '{{trans('home.ticket_confirm')}}',
            }).then((result) => {
              if (result.value) {
                $.ajax({
                  method: 'DELETE',
                  url: url,
                  data: {_token: '{{csrf_token()}}'},
                  dataType: 'json',
                  success: function(ret) {
                    if (ret.status === 'success') {
                      swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
                    } else {
                      swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
                    }
                  },
                });
              }
            });
          }
        </script>
    @endcan
@endsection

