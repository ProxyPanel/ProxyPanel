@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/multi-select/multi-select.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">
                    {{ isset($ruleGroup) ? trans('admin.action.edit_item', ['attribute' => trans('model.rule_group.attribute')]) : trans('admin.action.add_item', ['attribute' =>
                     trans('model.rule_group.attribute')]) }}
                </h2>
                <div class="panel-actions">
                    <a href="{{route('admin.rule.group.index')}}" class="btn btn-danger">{{ trans('common.back') }}</a>
                </div>
            </div>
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::pull('successMsg')"/>
            @endif
            @if($errors->any())
                <x-alert type="danger" :message="$errors->all()"/>
            @endif
            <div class="panel-body">
                <form action="{{isset($ruleGroup) ? route('admin.rule.group.update', $ruleGroup) : route('admin.rule.group.store')}}" method="post"
                      enctype="multipart/form-data" class="form-horizontal">
                    @isset($ruleGroup)
                        @method('PUT')
                    @endisset @csrf
                    <div class="form-group row">
                        <label class="col-md-2 col-sm-3 col-form-label" for="name">{{ trans('model.rule_group.name') }}</label>
                        <div class="col-md-9 col-sm-9">
                            <input type="text" class="form-control" name="name" id="name"/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-sm-3 col-form-label" for="type">{{ trans('model.rule_group.type') }}</label>
                        <div class="col-md-10 col-sm-8">
                            <ul class="list-unstyled list-inline">
                                <li class="list-inline-item">
                                    <div class="radio-custom radio-primary">
                                        <input type="radio" name="type" id="block" value="1" checked/>
                                        <label for="block">{{ trans('admin.rule.group.type.off') }}</label>
                                    </div>
                                </li>
                                <li class="list-inline-item">
                                    <div class="radio-custom radio-primary">
                                        <input type="radio" name="type" id="unblock" value="0"/>
                                        <label for="unblock">{{ trans('admin.rule.group.type.on') }}</label>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-sm-3 col-form-label" for="rules">{{ trans('model.rule_group.rules') }}</label>
                        <div class="col-md-9 col-sm-9">
                            <div class="btn-group mb-20">
                                <button type="button" class="btn btn-primary" id="select-all">{{ trans('admin.select_all') }}</button>
                                <button type="button" class="btn btn-danger" id="deselect-all">{{ trans('admin.clear') }}</button>
                            </div>
                            <select class="form-control" name="rules[]" id="rules" data-plugin="multiSelect" multiple>
                                @foreach($rules as $rule)
                                    <option value="{{$rule->id}}">{{$rule->id . ' - ' . $rule->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-actions text-right">
                        <button type="submit" class="btn btn-success">{{ trans('common.submit') }}</button>
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
        @isset($ruleGroup)
        $(document).ready(function() {
          $('#name').val('{{$ruleGroup->name}}');
          $("input[name='type'][value='{{$ruleGroup->type}}']").click();
          $('#rules').multiSelect('select', @json(array_map('strval', $ruleGroup->rules()->get()->pluck('id')->toArray())));
        });
        @endisset
        // 权限列表
        $('#rules').multiSelect({
          selectableHeader: '<input type=\'text\' class=\'search-input form-control\' autocomplete=\'off\' placeholder=\'{{ trans('admin.unselected_hint') }}\'>',
          selectionHeader: '<input type=\'text\' class=\'search-input form-control\' autocomplete=\'off\' placeholder=\'{{ trans('admin.selected_hint') }}\'>',
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
          $('#rules').multiSelect('select_all');
          return false;
        });

        // 反选
        $('#deselect-all').click(function() {
          $('#rules').multiSelect('deselect_all');
          return false;
        });
    </script>
@endsection
