@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{{ trans('admin.logs.rule.title') }}</h2>
                @can('admin.rule.clear')
                    <div class="panel-actions">
                        <button onclick="clearLog()" class="btn btn-outline-primary">
                            <i class="icon wb-rubber" aria-hidden="true"></i>{{ trans('admin.logs.rule.clear_all') }}
                        </button>
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-xxl-1 col-lg-2 col-md-1 col-sm-4">
                        <input type="number" class="form-control" name="user_id" value="{{Request::query('user_id')}}" placeholder="{{ trans('model.user.id') }}"/>
                    </div>
                    <div class="form-group col-xxl-2 col-lg-3 col-md-3 col-sm-4">
                        <input type="text" class="form-control" name="username" value="{{Request::query('username')}}" placeholder="{{ trans('model.user.username') }}"/>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" id="node_id" name="node_id">
                            <option value="" hidden>{{ trans('model.node.attribute') }}</option>
                            @foreach($nodes as $node)
                                <option value="{{$node->id}}">{{$node->id . ' - ' . $node->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" id="rule_id" name="rule_id">
                            <option value="" hidden>{{ trans('model.rule.attribute') }}</option>
                            @foreach($rules as $rule)
                                <option value="{{$rule->id}}">{{$rule->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4 btn-group">
                        <button type="submit" class="btn btn-primary">{{ trans('common.search') }}</button>
                        <a href="{{route('admin.rule.log')}}" class="btn btn-danger">{{ trans('common.reset') }}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> UID</th>
                        <th> {{ trans('model.user.username') }}</th>
                        <th> {{ trans('model.node.attribute') }}</th>
                        <th> {{ trans('admin.logs.rule.name') }}</th>
                        <th> {{ trans('admin.logs.rule.reason') }}</th>
                        <th> {{ trans('admin.logs.rule.created_at') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($ruleLogs as $ruleLog)
                        <tr>
                            <td> {{$ruleLog->id}} </td>
                            <td> {{$ruleLog->user->id ?? '【'.trans('common.deleted_item', ['attribute' => trans('common.account')]).'】'}} </td>
                            <td> {{$ruleLog->user->username ?? '【'.trans('common.deleted_item', ['attribute' => trans('common.account')]).'】'}} </td>
                            <td> {{empty($ruleLog->node) ? '【'.trans('common.deleted_item', ['attribute' => trans('model.node.attribute')]).'】' : '【'.trans('model.node.attribute').'#: ' . $ruleLog->node_id . '】' . $ruleLog->node->name}} </td>
                            <td> {{$ruleLog->rule_id ? '⛔  ' . ($ruleLog->rule->name ?? '【'.trans('common.deleted_item', ['attribute' => trans('model.rule.attribute')]).'】') : trans('admin.logs.rule.tag')}} </td>
                            <td> {{$ruleLog->reason}} </td>
                            <td> {{$ruleLog->created_at}} </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.logs.counts', ['num' => $ruleLogs->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$ruleLogs->links()}}
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
      $(document).ready(function() {
        $('#node_id').val({{Request::query('node_id')}});
        $('#rule_id').val({{Request::query('rule_id')}});

        $('select').on('change', function() {
          this.form.submit();
        });
      });

      @can('admin.rule.clear')
      // 清除所有记录
      function clearLog() {
        swal.fire({
          title: '{{trans('common.warning')}}',
          text: '{{ trans('admin.logs.rule.clear_confirm') }}',
          icon: 'warning',
          showCancelButton: true,
          cancelButtonText: '{{trans('common.close')}}',
          confirmButtonText: '{{trans('common.confirm')}}',
        }).then((result) => {
          if (result.value) {
            $.post("{{route('admin.rule.clear')}}", {_token: '{{csrf_token()}}'}, function(ret) {
              if (ret.status === 'success') {
                swal.fire({
                  title: ret.message,
                  icon: 'success',
                  timer: 1000,
                  showConfirmButton: false,
                }).then(() => window.location.reload());
              } else {
                swal.fire({title: ret.message, icon: 'error'});
              }
            });
          }
        });
      }
        @endcan
    </script>
@endsection
