@extends('admin.layouts')
@section('css')
    <script src="//at.alicdn.com/t/font_682457_e6aq10jsbq0yhkt9.js"></script>
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h1 class="panel-title"><i class="icon wb-settings" aria-hidden="true"></i>通用配置</h1>
            </div>
            <div class="panel-body container-fluid">
                <div class="nav-tabs-vertical" data-plugin="tabs">
                    <ul class="nav nav-tabs mr-25" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" data-toggle="tab" href="#method" aria-controls="method" role="tab">加密</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#protocol" aria-controls="protocol" role="tab">协议</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#obfs" aria-controls="obfs" role="tab">混淆</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#level" aria-controls="level" role="tab">等级</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#country" aria-controls="country" role="tab">国家地区</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href="#label" aria-controls="label" role="tab">标签</a>
                        </li>
                    </ul>
                    <div class="tab-content py-15">
                        <div class="tab-pane active" id="method" role="tabpanel">
                            <button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_config_modal">
                                新增<i class="icon wb-plus"></i>
                            </button>
                            <table class="text-md-center" data-toggle="table" data-height="700" data-virtual-scroll="true" data-mobile-responsive="true">
                                <thead class="thead-default">
                                <tr>
                                    <th> 名称</th>
                                    <th> 操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($methodList as $method)
                                    <tr>
                                        <td> {{$method->name}}</td>
                                        <td>
                                            @if($method->is_default)
                                                <span class='badge badge-lg badge-default'>默认</span>
                                            @else
                                                <div class="btn-group">
                                                    <button class="btn btn-primary" onclick="setDefault('{{$method->id}}')">
                                                        默认
                                                    </button>
                                                    <button class="btn btn-danger" onclick="delConfig('{{$method->id}}','{{$method->name}}')">
                                                        <i class="icon wb-trash"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane" id="protocol" role="tabpanel">
                            <button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_config_modal">
                                新增<i class="icon wb-plus"></i>
                            </button>
                            <table class="text-md-center" data-toggle="table" data-height="700" data-virtual-scroll="true" data-mobile-responsive="true">
                                <thead class="thead-default">
                                <tr>
                                    <th> 名称</th>
                                    <th> 操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($protocolList as $protocol)
                                    <tr>
                                        <td> {{$protocol->name}}</td>
                                        <td>
                                            @if($protocol->is_default)
                                                <span class="badge badge-lg badge-default">默认</span>
                                            @else
                                                <div class="btn-group">
                                                    <button class="btn btn-primary" onclick="setDefault('{{$protocol->id}}')">
                                                        默认
                                                    </button>
                                                    <button class="btn btn-danger" onclick="delConfig('{{$protocol->id}}','{{$protocol->name}}')">
                                                        <i class="icon wb-trash"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane" id="obfs" role="tabpanel">
                            <button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_config_modal">
                                新增<i class="icon wb-plus"></i>
                            </button>
                            <table class="text-md-center" data-toggle="table" data-height="700" data-virtual-scroll="true" data-mobile-responsive="true">
                                <thead class="thead-default">
                                <tr>
                                    <th> 名称</th>
                                    <th> 操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($obfsList as $obfs)
                                    <tr>
                                        <td> {{$obfs->name}}</td>
                                        <td>
                                            @if($obfs->is_default)
                                                <span class="badge badge-lg badge-default">默认</span>
                                            @else
                                                <div class="btn-group">
                                                    <button class="btn btn-primary" onclick="setDefault('{{$obfs->id}}')">
                                                        默认
                                                    </button>
                                                    <button class="btn btn-danger" onclick="delConfig('{{$obfs->id}}','{{$obfs->name}}')">
                                                        <i class="icon wb-trash"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane" id="level" role="tabpanel">
                            <button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_level_modal">
                                新增<i class="icon wb-plus"></i>
                            </button>
                            <table class="text-md-center" data-toggle="table" data-height="700" data-virtual-scroll="true" data-mobile-responsive="true">
                                <thead class="thead-default">
                                <tr>
                                    <th> 等级</th>
                                    <th> 名称</th>
                                    <th> 操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($levelList as $level)
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control" name="level" id="level_{{$level->id}}" value="{{$level->level}}"/>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="level_name" id="level_name_{{$level->id}}" value="{{$level->name}}"/>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-primary" onclick="updateLevel('{{$level->id}}')">
                                                    <i class="icon wb-edit" aria-hidden="true"></i></button>
                                                <button type="button" class="btn btn-danger" onclick="delLevel('{{$level->id}}','{{$level->name}}')">
                                                    <i class="icon wb-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane" id="country" role="tabpanel">
                            <button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_country_modal">
                                新增<i class="icon wb-plus"></i>
                            </button>
                            <table class="text-md-center" data-toggle="table" data-height="700" data-virtual-scroll="true" data-mobile-responsive="true">
                                <thead class="thead-default">
                                <tr>
                                    <th> 图标</th>
                                    <th> 代码</th>
                                    <th> 国家/地区名称</th>
                                    <th> 操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($countryList as $country)
                                    <tr>
                                        <td>
                                            <svg class="w-40 h-40 text-center" aria-hidden="true">
                                                <use xlink:href="@if($country->code)#icon-{{$country->code}}@endif"></use>
                                            </svg>
                                        </td>
                                        <td>
                                            {{$country->code}}
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="country_name" id="country_{{$country->code}}" value="{{$country->name}}"/>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-primary" onclick="updateCountry('{{$country->code}}')">
                                                    <i class="icon wb-edit" aria-hidden="true"></i></button>
                                                <button type="button" class="btn btn-danger" onclick="delCountry('{{$country->code}}','{{$country->name}}')">
                                                    <i class="icon wb-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane" id="label" role="tabpanel">
                            <button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_label_modal">
                                新增<i class="icon wb-plus"></i>
                            </button>
                            <table class="text-md-center" data-toggle="table" data-height="700" data-virtual-scroll="true" data-mobile-responsive="true">
                                <thead class="thead-default">
                                <tr>
                                    <th> 名称</th>
                                    <th> 关联节点数</th>
                                    <th> 排序</th>
                                    <th> 操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($labelList as $label)
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control" name="label_name" id="label_name_{{$label->id}}" value="{{$label->name}}"/>
                                        </td>
                                        <td> {{$label->nodes->count()}} </td>
                                        <td>
                                            <input type="number" class="form-control" name="label_sort" id="label_sort_{{$label->id}}" value="{{$label->sort}}"/>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-primary" onclick="updateLabel('{{$label->id}}')">
                                                    <i class="icon wb-edit" aria-hidden="true"></i></button>
                                                <button type="button" class="btn btn-danger" onclick="delLabel('{{$label->id}}','{{$label->name}}')">
                                                    <i class="icon wb-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add_config_modal" aria-hidden="true" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-simple modal-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title">新增配置</h4>
                </div>
                <form action="#" method="post" class="modal-body">
                    <div class="alert alert-danger" style="display: none;" id="msg"></div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <select class="form-control" name="type" id="type" placeholder="类型">
                                <option value="1" selected>加密方式</option>
                                <option value="2">协议</option>
                                <option value="3">混淆</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <input type="text" class="form-control" name="name" id="name" placeholder="名称">
                        </div>
                    </div>
                </form>
                <div class="modal-footer">
                    <button class="btn btn-danger" data-dismiss="modal">关 闭</button>
                    <button class="btn btn-primary" onclick="addConfig()">提 交</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add_level_modal" aria-hidden="true" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-simple modal-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title">新增等级</h4>
                </div>
                <form action="#" method="post" class="modal-body">
                    <div class="alert alert-danger" style="display: none;" id="level_msg"></div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <input type="text" class="form-control" name="level" id="add_level" placeholder="等级">
                        </div>
                        <div class="col-md-6 form-group">
                            <input type="text" class="form-control" name="level_name" id="add_level_name" placeholder="等级名称">
                        </div>
                    </div>
                </form>
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-danger">关 闭</button>
                    <button class="btn btn-primary" onclick="addLevel()">提 交</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add_country_modal" aria-hidden="true" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-simple modal-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title">新增国家/地区</h4>
                </div>
                <form action="#" method="post" class="modal-body">
                    <div class="alert alert-danger" style="display: none;" id="country_msg"></div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <input type="text" class="form-control" name="country_code" id="add_country_code" placeholder="ISO国家代码">
                        </div>
                        <div class="col-md-6 form-group">
                            <input type="text" class="form-control" name="country_name" id="add_country_name" placeholder=" 国家/地区名称">
                        </div>
                    </div>
                </form>
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-danger">关 闭</button>
                    <button class="btn btn-primary" onclick="addCountry()">提 交</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add_label_modal" aria-hidden="true" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-simple modal-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title">新增标签</h4>
                </div>
                <form action="#" method="post" class="modal-body">
                    <div class="alert alert-danger" style="display: none;" id="lable_msg"></div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <input type="text" class="form-control" name="label" id="add_label" placeholder="标签">
                        </div>
                        <div class="col-md-6 form-group">
                            <input type="text" class="form-control" name="label_sort" id="add_label_sort" placeholder="排序">
                        </div>
                    </div>
                </form>
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-danger">关 闭</button>
                    <button class="btn btn-primary" onclick="addLabel()">提 交</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/custom/jump-tab.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script>
        @can('admin.config.level.store')
        // 添加等级
        function addLevel() {
          const level = $('#add_level').val();
          const level_name = $('#add_level_name').val();

          if (level.trim() === '') {
            $('#level_msg').show().html('等级不能为空');
            $('#level').focus();
            return false;
          }

          if (level_name.trim() === '') {
            $('#level_msg').show().html('等级名称不能为空');
            $('#level_name').focus();
            return false;
          }

          $.ajax({
            url: '{{route('admin.config.level.store')}}',
            method: 'POST',
            data: {_token: '{{csrf_token()}}', level: level, level_name: level_name},
            beforeSend: function() {
              $('#level_msg').show().html('正在添加');
            },
            success: function(ret) {
              if (ret.status === 'fail') {
                $('#level_msg').show().html(ret.message);
                return false;
              }
              $('#add_level_modal').modal('hide');
              window.location.reload();
            },
            error: function() {
              $('#level_msg').show().html('请求错误，请重试');
            },
            complete: function() {
              swal.fire({title: '添加成功', icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
            },
          });
        }

        @else
        swal.fire({title: '您没有权限修改参数！', icon: 'error', timer: 1500, showConfirmButton: false});
        @endcan

        @can('admin.config.level.update')
        // 更新等级
        function updateLevel(id) {
          $.ajax({
            method: 'PUT',
            url: '{{route('admin.config.level.update', '')}}/' + id,
            data: {
              _token: '{{csrf_token()}}',
              level: $('#level_' + id).val(),
              level_name: $('#level_name_' + id).val(),
            },
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

        @else
        swal.fire({title: '您没有权限修改参数！', icon: 'error', timer: 1500, showConfirmButton: false});
        @endcan

        @can('admin.config.level.destroy')
        // 删除等级
        function delLevel(id, name) {
          swal.fire({
            title: '确定删除等级 【' + name + '】 ？',
            icon: 'question',
            allowEnterKey: false,
            showCancelButton: true,
            cancelButtonText: '{{trans('home.ticket_close')}}',
            confirmButtonText: '{{trans('home.ticket_confirm')}}',
          }).then((result) => {
            if (result.value) {
              $.ajax({
                method: 'DELETE',
                url: '{{route('admin.config.level.destroy', '')}}/' + id,
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

        @else
        swal.fire({title: '您没有权限修改参数！', icon: 'error', timer: 1500, showConfirmButton: false});
        @endcan

        @can('admin.config.country.store')
        // 添加国家/地区
        function addCountry() {
          const country_name = $('#add_country_name').val();
          const country_code = $('#add_country_code').val();

          if (country_code.trim() === '') {
            $('#country_msg').show().html('国家/地区代码不能为空');
            $('#add_country_code').focus();
            return false;
          }

          if (country_name.trim() === '') {
            $('#country_msg').show().html('国家/地区名称不能为空');
            $('#add_country_name').focus();
            return false;
          }

          $.ajax({
            url: '{{route('admin.config.country.store')}}',
            method: 'POST',
            data: {_token: '{{csrf_token()}}', code: country_code, name: country_name},
            beforeSend: function() {
              $('#country_msg').show().html('正在添加');
            },
            success: function(ret) {
              if (ret.status === 'fail') {
                $('#country_msg').show().html(ret.message);
                return false;
              }
              $('#add_country_modal').modal('hide');
              window.location.reload();
            },
            error: function() {
              $('#country_msg').show().html('请求错误，请重试');
            },
            complete: function() {
              swal.fire({
                title: '添加成功',
                icon: 'success',
                timer: 1000,
                showConfirmButton: false,
              }).then(() => window.location.reload());
            },
          });
        }

        @else
        swal.fire({title: '您没有权限修改参数！', icon: 'error', timer: 1500, showConfirmButton: false});
        @endcan

        @can('admin.config.country.update')
        // 更新国家/地区
        function updateCountry(code) {
          $.ajax({
            method: 'PUT',
            url: '{{route('admin.config.country.update', '')}}/' + code,
            data: {_token: '{{csrf_token()}}', name: $('#country_' + code).val()},
            dataType: 'json',
            success: function(ret) {
              if (ret.status === 'success') {
                swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
              } else {
                swal.fire({title: ret.message, icon: 'error'});
              }
            },
          });
        }

        @else
        swal.fire({title: '您没有权限修改参数！', icon: 'error', timer: 1500, showConfirmButton: false});
        @endcan

        @can('admin.config.country.destroy')
        // 删除国家/地区
        function delCountry(code, name) {
          swal.fire({
            title: '确定删除 【' + name + '】 信息？',
            icon: 'question',
            allowEnterKey: false,
            showCancelButton: true,
            cancelButtonText: '{{trans('home.ticket_close')}}',
            confirmButtonText: '{{trans('home.ticket_confirm')}}',
          }).then((result) => {
            if (result.value) {
              $.ajax({
                method: 'DELETE',
                url: '{{route('admin.config.country.destroy', '')}}/' + code,
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

        @else
        swal.fire({title: '您没有权限修改参数！', icon: 'error', timer: 1500, showConfirmButton: false});
        @endcan

        @can('admin.config.ss.store')
        // 添加配置
        function addConfig() {
          const name = $('#name').val();
          const type = $('#type').val();

          if (name.trim() === '') {
            $('#msg').show().html('名称不能为空');
            $('#name').focus();
            return false;
          }

          $.ajax({
            url: '{{route('admin.config.ss.store')}}',
            method: 'POST',
            data: {_token: '{{csrf_token()}}', name: name, type: type},
            dataType: 'json',
            beforeSend: function() {
              $('#msg').show().html('正在添加');
            },
            success: function(ret) {
              if (ret.status === 'fail') {
                $('#msg').show().html(ret.message);
                return false;
              }

              $('#add_config_modal').modal('hide');
            },
            error: function() {
              $('#msg').show().html('请求错误，请重试');
            },
            complete: function() {
              swal.fire({title: '添加成功', icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
            },
          });
        }

        @else
        swal.fire({title: '您没有权限修改参数！', icon: 'error', timer: 1500, showConfirmButton: false});
        @endcan

        @can('admin.config.ss.update')
        // 置为默认
        function setDefault(id) {
          $.ajax({
            method: 'PUT',
            url: '{{route('admin.config.ss.update', '')}}/' + id,
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

        @else
        swal.fire({title: '您没有权限修改参数！', icon: 'error', timer: 1500, showConfirmButton: false});
        @endcan

        @can('admin.config.ss.destroy')
        // 删除配置
        function delConfig(id, name) {
          swal.fire({
            title: '确定删除配置 【' + name + '】 ？',
            icon: 'question',
            allowEnterKey: false,
            showCancelButton: true,
            cancelButtonText: '{{trans('home.ticket_close')}}',
            confirmButtonText: '{{trans('home.ticket_confirm')}}',
          }).then((result) => {
            if (result.value) {
              $.ajax({
                method: 'DELETE',
                url: '{{route('admin.config.ss.destroy', '')}}/' + id,
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

        @else
        swal.fire({title: '您没有权限修改参数！', icon: 'error', timer: 1500, showConfirmButton: false});
        @endcan

        @can('admin.config.label.store')
        // 添加标签
        function addLabel() {
          const name = $('#add_label').val();
          const sort = $('#add_label_sort').val();

          if (name.trim() === '') {
            $('#lable_msg').show().html('标签不能为空');
            return false;
          }

          if (sort.trim() === '') {
            $('#lable_msg').show().html('标签排序不能为空');
            return false;
          }

          $.ajax({
            url: '{{route('admin.config.label.store')}}',
            method: 'POST',
            data: {_token: '{{csrf_token()}}', name: name, sort: sort},
            beforeSend: function() {
              $('#level_msg').show().html('正在添加');
            },
            success: function(ret) {
              if (ret.status === 'fail') {
                $('#lable_msg').show().html(ret.message);
                return false;
              }
              $('#add_label_modal').modal('hide');
              window.location.reload();
            },
            error: function() {
              $('#lable_msg').show().html('请求错误，请重试');
            },
            complete: function() {
              swal.fire({
                title: '添加成功',
                icon: 'success',
                timer: 1000,
                showConfirmButton: false,
              }).then(() => window.location.reload());
            },
          });
        }

        @else
        swal.fire({title: '您没有权限修改参数！', icon: 'error', timer: 1500, showConfirmButton: false});
        @endcan

        @can('admin.config.label.update')
        // 编辑标签
        function updateLabel(id) {
          $.ajax({
            method: 'PUT',
            url: '{{route('admin.config.label.update', '')}}/' + id,
            data: {
              _token: '{{csrf_token()}}',
              name: $('#label_name_' + id).val(),
              sort: $('#label_sort_' + id).val(),
            },
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

        @else
        swal.fire({title: '您没有权限修改参数！', icon: 'error', timer: 1500, showConfirmButton: false});
        @endcan

        @can('admin.config.label.destroy')
        // 删除标签
        function delLabel(id, name) {
          swal.fire({
            title: '警告',
            text: '确定删除标签 【' + name + '】 ?',
            icon: 'warning',
            showCancelButton: true,
            cancelButtonText: '{{trans('home.ticket_close')}}',
            confirmButtonText: '{{trans('home.ticket_confirm')}}',
          }).then((result) => {
            $.ajax({
              method: 'DELETE',
              url: '{{route('admin.config.label.destroy', '')}}/' + id,
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
          });
        }

        @else
        swal.fire({title: '您没有权限修改参数！', icon: 'error', timer: 1500, showConfirmButton: false});
        @endcan
    </script>
@endsection
