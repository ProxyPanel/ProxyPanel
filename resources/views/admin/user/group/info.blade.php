@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/multi-select/multi-select.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <x-ui.panel :title="trans(isset($user) ? 'admin.action.edit_item' : 'admin.action.add_item', ['attribute' => trans('model.user_group.attribute')])" icon="wb-users">
            <x-slot:actions>
                <a class="btn btn-danger" href="{{ route('admin.user.group.index') }}">{{ trans('common.back') }}</a>
            </x-slot:actions>
            <x-slot:alert>
                @if (Session::has('successMsg'))
                    <x-alert :message="Session::pull('successMsg')" />
                @endif
                @if ($errors->any())
                    <x-alert type="danger" :message="$errors->all()" />
                @endif
            </x-slot:alert>
            <x-admin.form.container :route="isset($group) ? route('admin.user.group.update', $group['id']) : route('admin.user.group.store')" :method="isset($group) ? 'PUT' : 'POST'">
                <x-admin.form.input name="name" :label="trans('model.user_group.name')" required />
                <x-admin.form.skeleton name="nodes" :label="trans('model.user_group.nodes')" input_grid="col-md-8">
                    <div class="btn-group mb-20">
                        <button class="btn btn-primary" id="select-all" type="button">{{ trans('admin.select_all') }}</button>
                        <button class="btn btn-danger" id="deselect-all" type="button">{{ trans('admin.clear') }}</button>
                    </div>
                    <select class="form-control" id="nodes" name="nodes[]" data-plugin="multiSelect" multiple>
                        @foreach ($nodes as $id => $name)
                            <option value="{{ $id }}">{{ $id . ' - ' . $name }}</option>
                        @endforeach
                    </select>
                </x-admin.form.skeleton>
                <div class="form-actions text-right col-12">
                    <button class="btn btn-success" type="submit">{{ trans('common.submit') }}</button>
                </div>
            </x-admin.form.container>
        </x-ui.panel>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/multi-select/jquery.multi-select.min.js"></script>
    <script src="/assets/global/js/Plugin/multi-select.js"></script>
    <script src="/assets/custom/jquery.quicksearch.min.js"></script>
    <script>
        let groupData = {};

        @isset($group)
            groupData = @json($group)
        @endisset
        @if (old())
            groupData = @json(old())
        @endif

        $(document).ready(function() {
            autoPopulateForm(groupData); // 填充表单数据
        });

        // 权限列表
        $('#nodes').multiSelect({
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
