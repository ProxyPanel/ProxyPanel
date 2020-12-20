@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/multi-select/multi-select.min.css" rel="stylesheet">
    <style>
        .ms-container {
            width: auto;
        }
    </style>
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">@isset($role)编辑@else添加@endisset角色</h2>
                <div class="panel-actions">
                    <a href="{{route('admin.role.index')}}" class="btn btn-danger">返 回</a>
                </div>
            </div>
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::get('successMsg')"/>
            @endif
            @if($errors->any())
                <x-alert type="danger" :message="$errors->all()"/>
            @endif
            <div class="panel-body">
                <form action="@isset($role){{route('admin.role.update',$role)}}@else{{route('admin.role.store')}}@endisset"
                      method="POST" enctype="multipart/form-data" class="form-horizontal">
                    @isset($role)@method('PUT')@endisset
                    @csrf
                    <div class="form-group row">
                        <label class="col-md-2 col-sm-3 col-form-label" for="description">显示名称</label>
                        <div class="col-md-5 col-sm-9">
                            <input type="text" class="form-control" name="description" id="description"/>
                            <span class="text-help"> 名称，例如：管理员 </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-sm-3 col-form-label" for="name">内部名称</label>
                        <div class="col-md-5 col-sm-9">
                            <input type="text" class="form-control" name="name" id="name"/>
                            <span class="text-help"> 名称，例如：Administrator </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-sm-3 col-form-label" for="permissions">选择权限</label>
                        <div class="col-md-9 col-sm-9">
                            <div class="btn-group mb-20">
                                <button type="button" class="btn btn-primary" id="select-all">全 选</button>
                                <button type="button" class="btn btn-danger" id="deselect-all">清 空</button>
                            </div>
                            <select class="form-control mx-auto w-p100" name="permissions[]" id="permissions" data-plugin="multiSelect" multiple>
                                @foreach($permissions as $key => $description)
                                    <option value="{{ $key }}">{{ $description .' - '. $key }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-actions text-right">
                        <button type="submit" class="btn btn-success">提 交</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/multi-select/jquery.multi-select.min.js"></script>
    <script src="/assets/global/js/Plugin/multi-select.js"></script>
    <script src="/assets/custom/jquery.quicksearch.min.js"></script>
    <script>
        @isset($role)
        $(document).ready(function() {
          $('#description').val('{{$role->description}}');
          $('#name').val('{{$role->name}}');
          $('#permissions').multiSelect('select',@json($role->permissions->pluck('name')));
        });
        @endisset
        // 权限列表
        $('#permissions').multiSelect({
          selectableHeader: '<input type=\'text\' class=\'search-input form-control\' autocomplete=\'off\' placeholder=\'待分配规则，此处可搜索\'>',
          selectionHeader: '<input type=\'text\' class=\'search-input form-control\' autocomplete=\'off\' placeholder=\'已分配规则，此处可搜索\'>',
          afterInit: function() {
            const that = this,
                $selectableSearch = that.$selectableUl.prev(),
                $selectionSearch = that.$selectionUl.prev(),
                selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
                selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

            that.qs1 = $selectableSearch.quicksearch(selectableSearchString).on('keydown', function(e) {
              if (e.which === 40) {
                that.$selectableUl.focus();
                return false;
              }
            });

            that.qs2 = $selectionSearch.quicksearch(selectionSearchString).on('keydown', function(e) {
              if (e.which === 40) {
                that.$selectionUl.focus();
                return false;
              }
            });
          },
          afterSelect: function() {
            this.qs1.cache();
            this.qs2.cache();
          },
          afterDeselect: function() {
            this.qs1.cache();
            this.qs2.cache();
          },
        });

        // 全选
        $('#select-all').click(function() {
          $('#permissions').multiSelect('select_all');
          return false;
        });

        // 反选
        $('#deselect-all').click(function() {
          $('#permissions').multiSelect('deselect_all');
          return false;
        });
    </script>
@endsection