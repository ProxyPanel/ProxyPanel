@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/multi-select/multi-select.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">分配节点</h2>
                <div class="panel-actions">
                    <a href="{{route('admin.rule.group.index')}}" class="btn btn-danger">返 回</a>
                </div>
            </div>
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::get('successMsg')"/>
            @endif
            @if($errors->any())
                <x-alert type="danger" :message="$errors->all()"/>
            @endif
            <div class="panel-body">
                <form action={{route('admin.rule.group.assign', $ruleGroup)}} method="post" enctype="multipart/form-data" class="form-horizontal">
                    @method('PUT')@csrf
                    <div class="form-group row">
                        <label class="col-md-2 col-sm-3 col-form-label" for="name">所属分组</label>
                        <div class="col-auto">
                            <input class="form-control" id="name" value="{{$ruleGroup->name}}" readonly/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-sm-3 col-form-label" for="nodes">选择节点</label>
                        <div class="col-md-9 col-sm-9">
                            <div class="btn-group mb-20">
                                <button type="button" class="btn btn-primary" id="select-all">全 选</button>
                                <button type="button" class="btn btn-danger" id="deselect-all">清 空</button>
                            </div>
                            <select class="form-control" name="nodes[]" id="nodes" data-plugin="multiSelect" multiple>
                                @foreach($nodeList as $node)
                                    <option value="{{$node->id}}">{{$node->id . ' - ' . Str::limit($node->name, 30)}}</option>
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
      $(document).ready(function() {
        $('#nodes').multiSelect('select',@json($ruleGroup->nodes));
      });

      // 权限列表
      $('#nodes').multiSelect({
        selectableHeader: '<input type=\'text\' class=\'search-input form-control\' autocomplete=\'off\' placeholder=\'待分配节点，此处可搜索\'>',
        selectionHeader: '<input type=\'text\' class=\'search-input form-control\' autocomplete=\'off\' placeholder=\'已分配节点，此处可搜索\'>',
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
        $('#nodes').multiSelect('select_all');
        return false;
      });

      // 反选
      $('#deselect-all').click(function() {
        $('#nodes').multiSelect('deselect_all');
        return false;
      });
    </script>
@endsection
