@extends('admin.table_layouts')
@section('content')
    <div class="page-content container">
        <x-ui.panel icon="wb-settings" :title="trans('admin.menu.setting.universal')">
            <div class="nav-tabs-vertical" data-plugin="tabs">
                <ul class="nav nav-tabs mr-25" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" data-toggle="tab" href="#method" role="tab" aria-controls="method">{{ trans('model.node.method') }}</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-toggle="tab" href="#protocol" role="tab" aria-controls="protocol">{{ trans('model.node.protocol') }}</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-toggle="tab" href="#obfs" role="tab" aria-controls="obfs">{{ trans('model.node.obfs') }}</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-toggle="tab" href="#level" role="tab" aria-controls="level">{{ trans('model.common.level') }}</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-toggle="tab" href="#country" role="tab" aria-controls="country">{{ trans('model.node.country') }}</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-toggle="tab" href="#label" role="tab" aria-controls="label">{{ trans('model.node.label') }}</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-toggle="tab" href="#category" role="tab" aria-controls="category">{{ trans('model.goods.category') }}</a>
                    </li>
                </ul>
                <div class="tab-content py-15">
                    <div class="tab-pane active" id="method" role="tabpanel">
                        @can('admin.config.ss.store')
                            <button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_config_modal">
                                <i class="icon wb-plus"></i>
                            </button>
                        @endcan
                        <table class="text-md-center" data-toggle="table" data-height="700" data-mobile-responsive="true">
                            <thead class="thead-default">
                                <tr>
                                    <th> {{ ucfirst(trans('validation.attributes.name')) }}</th>
                                    <th> {{ trans('common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($methods as $method)
                                    <tr>
                                        <td> {{ $method->name }}</td>
                                        <td>
                                            @if ($method->is_default)
                                                <span class='badge badge-lg badge-default'>{{ trans('common.default') }}</span>
                                            @else
                                                <div class="btn-group">
                                                    @can('admin.config.ss.update')
                                                        <button class="btn btn-primary" onclick="setDefault('{{ $method->id }}')">
                                                            {{ trans('admin.setting.common.set_default') }}
                                                        </button>
                                                    @endcan
                                                    @can('admin.config.ss.destroy')
                                                        <button class="btn btn-danger" onclick="delConfig('{{ $method->id }}','{{ $method->name }}')">
                                                            <i class="icon wb-trash"></i>
                                                        </button>
                                                    @endcan
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane" id="protocol" role="tabpanel">
                        @can('admin.config.ss.store')
                            <button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_config_modal">
                                <i class="icon wb-plus"></i>
                            </button>
                        @endcan
                        <table class="text-md-center" data-toggle="table" data-height="700" data-mobile-responsive="true">
                            <thead class="thead-default">
                                <tr>
                                    <th> {{ ucfirst(trans('validation.attributes.name')) }}</th>
                                    <th> {{ trans('common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($protocols as $protocol)
                                    <tr>
                                        <td> {{ $protocol->name }}</td>
                                        <td>
                                            @if ($protocol->is_default)
                                                <span class="badge badge-lg badge-default">{{ trans('common.default') }}</span>
                                            @else
                                                <div class="btn-group">
                                                    @can('admin.config.ss.update')
                                                        <button class="btn btn-primary" onclick="setDefault('{{ $protocol->id }}')">
                                                            {{ trans('admin.setting.common.set_default') }}
                                                        </button>
                                                    @endcan
                                                    @can('admin.config.ss.destroy')
                                                        <button class="btn btn-danger" onclick="delConfig('{{ $protocol->id }}','{{ $protocol->name }}')">
                                                            <i class="icon wb-trash"></i>
                                                        </button>
                                                    @endcan
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane" id="obfs" role="tabpanel">
                        @can('admin.config.ss.store')
                            <button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_config_modal">
                                <i class="icon wb-plus"></i>
                            </button>
                        @endcan
                        <table class="text-md-center" data-toggle="table" data-height="700" data-mobile-responsive="true">
                            <thead class="thead-default">
                                <tr>
                                    <th> {{ ucfirst(trans('validation.attributes.name')) }}</th>
                                    <th> {{ trans('common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($obfsList as $obfs)
                                    <tr>
                                        <td> {{ $obfs->name }}</td>
                                        <td>
                                            @if ($obfs->is_default)
                                                <span class="badge badge-lg badge-default">{{ trans('common.default') }}</span>
                                            @else
                                                <div class="btn-group">
                                                    @can('admin.config.ss.update')
                                                        <button class="btn btn-primary" onclick="setDefault('{{ $obfs->id }}')">
                                                            {{ trans('admin.setting.common.set_default') }}
                                                        </button>
                                                    @endcan
                                                    @can('admin.config.ss.destroy')
                                                        <button class="btn btn-danger" onclick="delConfig('{{ $obfs->id }}','{{ $obfs->name }}')">
                                                            <i class="icon wb-trash"></i>
                                                        </button>
                                                    @endcan
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane" id="level" role="tabpanel">
                        @can('admin.config.level.store')
                            <button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_level_modal">
                                <i class="icon wb-plus"></i>
                            </button>
                        @endcan
                        <table class="text-md-center" data-toggle="table" data-height="700" data-mobile-responsive="true">
                            <thead class="thead-default">
                                <tr>
                                    <th> {{ trans('model.common.level') }}</th>
                                    <th> {{ ucfirst(trans('validation.attributes.name')) }}</th>
                                    <th> {{ trans('common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($levels as $level)
                                    <tr>
                                        <td>
                                            <input class="form-control" id="level_{{ $level->id }}" name="level" type="text"
                                                   value="{{ $level->level }}" />
                                        </td>
                                        <td>
                                            <input class="form-control" id="level_name_{{ $level->id }}" name="level_name" type="text"
                                                   value="{{ $level->name }}" />
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                @can('admin.config.level.update')
                                                    <button class="btn btn-primary" type="button" onclick="updateLevel('{{ $level->id }}')">
                                                        <i class="icon wb-edit" aria-hidden="true"></i></button>
                                                @endcan
                                                @can('admin.config.level.destroy')
                                                    <button class="btn btn-danger" type="button" onclick="delLevel('{{ $level->id }}','{{ $level->name }}')">
                                                        <i class="icon wb-trash"></i></button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane" id="category" role="tabpanel">
                        @can('admin.config.category.store')
                            <button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_category_modal">
                                <i class="icon wb-plus"></i>
                            </button>
                        @endcan
                        <table class="text-md-center" data-toggle="table" data-height="700" data-mobile-responsive="true">
                            <thead class="thead-default">
                                <tr>
                                    <th> {{ ucfirst(trans('validation.attributes.name')) }}</th>
                                    <th> {{ trans('model.common.sort') }}</th>
                                    <th> {{ trans('common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                    <tr>
                                        <td>
                                            <input class="form-control" id="category_name_{{ $category->id }}" name="name" type="text"
                                                   value="{{ $category->name }}" />
                                        </td>
                                        <td>
                                            <input class="form-control" id="category_sort_{{ $category->id }}" name="sort" type="text"
                                                   value="{{ $category->sort }}" />
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                @can('admin.config.category.update')
                                                    <button class="btn btn-primary" type="button" onclick="updateCategory('{{ $category->id }}')">
                                                        <i class="icon wb-edit" aria-hidden="true"></i></button>
                                                @endcan
                                                @can('admin.config.category.destroy')
                                                    <button class="btn btn-danger" type="button"
                                                            onclick="delCategory('{{ $category->id }}','{{ $category->name }}')">
                                                        <i class="icon wb-trash"></i></button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane" id="country" role="tabpanel">
                        @can('admin.config.country.store')
                            <button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_country_modal">
                                <i class="icon wb-plus"></i>
                            </button>
                        @endcan
                        <table class="text-md-center" data-toggle="table" data-height="700" data-mobile-responsive="true">
                            <thead class="thead-default">
                                <tr>
                                    <th> {{ trans('model.country.icon') }}</th>
                                    <th> {{ trans('model.country.code') }}</th>
                                    <th> {{ trans('model.country.name') }}</th>
                                    <th> {{ trans('common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($countries as $country)
                                    <tr>
                                        <td>
                                            <i class="fi fis fi-{{ $country->code }} h-40 w-40" aria-hidden="true"></i>
                                        </td>
                                        <td>
                                            {{ $country->code }}
                                        </td>
                                        <td>
                                            <input class="form-control" id="country_{{ $country->code }}" name="country_name" type="text"
                                                   value="{{ $country->name }}" />
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                @can('admin.config.country.update')
                                                    <button class="btn btn-primary" type="button" onclick="updateCountry('{{ $country->code }}')">
                                                        <i class="icon wb-edit" aria-hidden="true"></i></button>
                                                @endcan
                                                @can('admin.config.country.destroy')
                                                    <button class="btn btn-danger" type="button"
                                                            onclick="delCountry('{{ $country->code }}','{{ $country->name }}')">
                                                        <i class="icon wb-trash"></i></button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane" id="label" role="tabpanel">
                        @can('admin.config.label.store')
                            <button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_label_modal">
                                <i class="icon wb-plus"></i>
                            </button>
                        @endcan
                        <table class="text-md-center" data-toggle="table" data-height="700" data-mobile-responsive="true">
                            <thead class="thead-default">
                                <tr>
                                    <th> {{ ucfirst(trans('validation.attributes.name')) }}</th>
                                    <th> {{ trans('admin.setting.common.connect_nodes') }}</th>
                                    <th> {{ trans('model.common.sort') }}</th>
                                    <th> {{ trans('common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($labels as $label)
                                    <tr>
                                        <td>
                                            <input class="form-control" id="label_name_{{ $label->id }}" name="label_name" type="text"
                                                   value="{{ $label->name }}" />
                                        </td>
                                        <td> {{ $label->nodes_count }} </td>
                                        <td>
                                            <input class="form-control" id="label_sort_{{ $label->id }}" name="label_sort" type="number"
                                                   value="{{ $label->sort }}" />
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                @can('admin.config.label.update')
                                                    <button class="btn btn-primary" type="button" onclick="updateLabel('{{ $label->id }}')">
                                                        <i class="icon wb-edit" aria-hidden="true"></i></button>
                                                @endcan
                                                @can('admin.config.label.destroy')
                                                    <button class="btn btn-danger" type="button" onclick="delLabel('{{ $label->id }}','{{ $label->name }}')">
                                                        <i class="icon wb-trash"></i></button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-ui.panel>
    </div>

    @can('admin.config.ss.store')
        <x-ui.modal id="add_config_modal" form :title="trans('common.add')">
            <form class="modal-body" action="#" method="post">
                <div class="alert alert-danger" id="msg" style="display: none;"></div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <select class="form-control" id="type" name="type">
                            <option value="1" selected>{{ trans('model.node.method') }}</option>
                            <option value="2">{{ trans('model.node.protocol') }}</option>
                            <option value="3">{{ trans('model.node.obfs') }}</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <input class="form-control" id="name" name="name" type="text" placeholder="{{ ucfirst(trans('validation.attributes.name')) }}">
                    </div>
                </div>
            </form>
            <x-slot:actions>
                <button class="btn btn-primary" onclick="addConfig()">{{ trans('common.submit') }}</button>
            </x-slot:actions>
        </x-ui.modal>
    @endcan

    @can('admin.config.level.store')
        <x-ui.modal id="add_level_modal" form :title="trans('admin.action.add_item', ['attribute' => trans('model.common.level')])">
            <form class="modal-body" action="#" method="post">
                <div class="alert alert-danger" id="level_msg" style="display: none;"></div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <input class="form-control" id="add_level" name="level" type="text" placeholder="{{ trans('model.common.level') }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <input class="form-control" id="add_level_name" name="level_name" type="text"
                               placeholder="{{ ucfirst(trans('validation.attributes.name')) }}">
                    </div>
                </div>
            </form>
            <x-slot:actions>
                <button class="btn btn-primary" onclick="addLevel()">{{ trans('common.submit') }}</button>
            </x-slot:actions>
        </x-ui.modal>
    @endcan

    @can('admin.config.category.store')
        <x-ui.modal id="add_category_modal" form :title="trans('admin.action.add_item', ['attribute' => trans('model.goods.category')])">
            <form class="modal-body" action="#" method="post">
                <div class="alert alert-danger" id="category_msg" style="display: none;"></div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <input class="form-control" id="add_category_name" name="name" type="text"
                               placeholder="{{ ucfirst(trans('validation.attributes.name')) }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <input class="form-control" id="add_category_sort" name="sort" type="text" placeholder="{{ trans('model.common.sort') }}">
                    </div>
                </div>
            </form>
            <x-slot:actions>
                <button class="btn btn-primary" onclick="addCategory()">{{ trans('common.submit') }}</button>
            </x-slot:actions>
        </x-ui.modal>
    @endcan

    @can('admin.config.country.store')
        <x-ui.modal id="add_country_modal" form :title="trans('admin.action.add_item', ['attribute' => trans('model.country.name')])">
            <form class="modal-body" action="#" method="post">
                <div class="alert alert-danger" id="country_msg" style="display: none;"></div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <input class="form-control" id="add_country_code" name="country_code" type="text" placeholder="{{ trans('model.country.code') }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <input class="form-control" id="add_country_name" name="country_name" type="text" placeholder="{{ trans('model.country.name') }}">
                    </div>
                </div>
            </form>
            <x-slot:actions>
                <button class="btn btn-primary" onclick="addCountry()">{{ trans('common.submit') }}</button>
            </x-slot:actions>
        </x-ui.modal>
    @endcan

    @can('admin.config.label.store')
        <x-ui.modal id="add_label_modal" form :title="trans('admin.action.add_item', ['attribute' => trans('model.node.label')])">
            <form class="modal-body" action="#" method="post">
                <div class="alert alert-danger" id="label_msg" style="display: none;"></div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <input class="form-control" id="add_label" name="label" type="text" placeholder="{{ ucfirst(trans('validation.attributes.name')) }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <input class="form-control" id="add_label_sort" name="label_sort" type="text" placeholder="{{ trans('model.common.sort') }}">
                    </div>
                </div>
            </form>
            <x-slot:actions>
                <button class="btn btn-primary" onclick="addLabel()">{{ trans('common.submit') }}</button>
            </x-slot:actions>
        </x-ui.modal>
    @endcan
@endsection
@push('javascript')
    <script src="/assets/custom/jump-tab.js"></script>
    <script>
        @can('admin.config.level.store')
            function addLevel() { // 添加等级
                const level = $("#add_level").val();
                const name = $("#add_level_name").val();

                if (level.trim() === "") {
                    $("#level_msg").show().html('{{ trans('validation.required', ['attribute' => trans('model.common.level')]) }}');
                    $("#add_level").focus();
                    return false;
                }

                if (name.trim() === "") {
                    $("#level_msg").show().html('{{ trans('validation.required', ['attribute' => ucfirst(trans('validation.attributes.name'))]) }}');
                    $("#add_level_name").focus();
                    return false;
                }

                ajaxPost("{{ route('admin.config.level.store') }}", {
                    level: level,
                    name: name
                }, {
                    beforeSend: function() {
                        $("#level_msg").show().html('{{ trans('admin.creating') }}');
                    },
                    success: function(ret) {
                        if (ret.status === "fail") {
                            $("#level_msg").show().html(ret.message);
                            return false;
                        }
                        $("#add_level_modal").modal("hide");
                        handleResponse(ret);
                    },
                    error: function() {
                        $("#level_msg").show().html('{{ trans('common.request_failed') }}');
                    }
                });
            }
        @endcan

        @can('admin.config.level.update')
            function updateLevel(id) { // 更新等级
                ajaxPut(jsRoute('{{ route('admin.config.level.update', 'PLACEHOLDER') }}', id), {
                    level: $(`#level_${id}`).val(),
                    name: $(`#level_name_${id}`).val()
                });
            }
        @endcan

        @can('admin.config.level.destroy')
            function delLevel(id, name) { // 删除配置
                confirmDelete(jsRoute('{{ route('admin.config.level.destroy', 'PLACEHOLDER') }}', id), name);
            }
        @endcan

        @can('admin.config.category.store')
            function addCategory() { // 添加分类
                const name = $("#add_category_name").val();
                const sort = $("#add_category_sort").val();

                if (name.trim() === "") {
                    $("#category_msg").show().html('{{ trans('validation.required', ['attribute' => ucfirst(trans('validation.attributes.name'))]) }}');
                    $("#add_category_name").focus();
                    return false;
                }

                if (sort.trim() === "") {
                    $("#category_msg").show().html('{{ trans('validation.required', ['attribute' => trans('model.common.sort')]) }}');
                    $("#add_category_sort").focus();
                    return false;
                }

                ajaxPost("{{ route('admin.config.category.store') }}", {
                    name: name,
                    sort: sort
                }, {
                    beforeSend: function() {
                        $("#category_msg").show().html('{{ trans('admin.creating') }}');
                    },
                    success: function(ret) {
                        if (ret.status === "fail") {
                            $("#category_msg").show().html(ret.message);
                            return false;
                        }
                        $("#add_category_modal").modal("hide");
                        handleResponse(ret);
                    },
                    error: function() {
                        $("#category_msg").show().html('{{ trans('common.request_failed') }}');
                    }
                });
            }
        @endcan

        @can('admin.config.category.update')
            function updateCategory(id) { // 更新分类
                ajaxPut(jsRoute('{{ route('admin.config.category.update', 'PLACEHOLDER') }}', id), {
                    name: $(`#category_name_${id}`).val(),
                    sort: $(`#category_sort_${id}`).val()
                });
            }
        @endcan

        @can('admin.config.category.destroy')
            function delCategory(id, name) { // 删除分类
                confirmDelete(jsRoute('{{ route('admin.config.category.destroy', 'PLACEHOLDER') }}', id), name);
            }
        @endcan

        @can('admin.config.country.store')
            function addCountry() { // 添加国家/地区
                const country_name = $("#add_country_name").val();
                const country_code = $("#add_country_code").val();

                if (country_code.trim() === "") {
                    $("#country_msg").show().html('{{ trans('validation.required', ['attribute' => ucfirst(trans('validation.attributes.national_code'))]) }}');
                    $("#add_country_code").focus();
                    return false;
                }

                if (country_name.trim() === "") {
                    $("#country_msg").show().html('{{ trans('validation.required', ['attribute' => trans('model.country.name')]) }}');
                    $("#add_country_name").focus();
                    return false;
                }

                ajaxPost("{{ route('admin.config.country.store') }}", {
                    code: country_code,
                    name: country_name
                }, {
                    beforeSend: function() {
                        $("#country_msg").show().html('{{ trans('admin.creating') }}');
                    },
                    success: function(ret) {
                        if (ret.status === "fail") {
                            $("#country_msg").show().html(ret.message);
                            return false;
                        }
                        $("#add_country_modal").modal("hide");
                        handleResponse(ret);
                    },
                    error: function() {
                        $("#country_msg").show().html('{{ trans('common.request_failed') }}');
                    }
                });
            }
        @endcan

        @can('admin.config.country.update')
            function updateCountry(code) { // 更新国家/地区
                ajaxPut(jsRoute('{{ route('admin.config.country.update', 'PLACEHOLDER') }}', code), {
                    name: $("#country_" + code).val()
                });
            }
        @endcan

        @can('admin.config.country.destroy')
            function delCountry(code, name) { // 删除国家/地区
                confirmDelete(jsRoute('{{ route('admin.config.country.destroy', 'PLACEHOLDER') }}', code), name, '{{ trans('model.node.country') }}');
            }
        @endcan

        @can('admin.config.ss.store')
            function addConfig() { // 添加配置
                const name = $("#name").val();
                const type = $("#type").val();

                if (name.trim() === "") {
                    $("#msg").show().html('{{ trans('validation.required', ['attribute' => ucfirst(trans('validation.attributes.name'))]) }}');
                    $("#name").focus();
                    return false;
                }

                ajaxPost("{{ route('admin.config.ss.store') }}", {
                    name: name,
                    type: type
                }, {
                    beforeSend: function() {
                        $("#msg").show().html('{{ trans('admin.creating') }}');
                    },
                    success: function(ret) {
                        if (ret.status === "fail") {
                            $("#msg").show().html(ret.message);
                            return false;
                        }
                        $("#add_config_modal").modal("hide");
                        handleResponse(ret);
                    },
                    error: function() {
                        $("#msg").show().html('{{ trans('common.request_failed') }}');
                    }
                });
            }
        @endcan

        @can('admin.config.ss.update')
            function setDefault(id) { // 置为默认
                ajaxPut(jsRoute('{{ route('admin.config.ss.update', 'PLACEHOLDER') }}', id));
            }
        @endcan

        @can('admin.config.ss.destroy')
            function delConfig(id, name) { // 删除配置
                confirmDelete(jsRoute('{{ route('admin.config.ss.destroy', 'PLACEHOLDER') }}', id), name);
            }
        @endcan

        @can('admin.config.label.store')
            function addLabel() { // 添加标签
                const name = $("#add_label").val();
                const sort = $("#add_label_sort").val();

                if (name.trim() === "") {
                    $("#label_msg").show().html('{{ trans('validation.required', ['attribute' => ucfirst(trans('validation.attributes.name'))]) }}');
                    return false;
                }

                if (sort.trim() === "") {
                    $("#label_msg").show().html('{{ trans('validation.required', ['attribute' => trans('model.common.sort')]) }}');
                    return false;
                }

                ajaxPost("{{ route('admin.config.label.store') }}", {
                    name: name,
                    sort: sort
                }, {
                    beforeSend: function() {
                        $("#label_msg").show().html('{{ trans('admin.creating') }}');
                    },
                    success: function(ret) {
                        if (ret.status === "fail") {
                            $("#label_msg").show().html(ret.message);
                            return false;
                        }
                        $("#add_label_modal").modal("hide");
                        handleResponse(ret);
                    },
                    error: function() {
                        $("#label_msg").show().html('{{ trans('common.request_failed') }}');
                    }
                });
            }
        @endcan

        @can('admin.config.label.update')
            function updateLabel(id) { // 编辑标签
                ajaxPut(jsRoute('{{ route('admin.config.label.update', 'PLACEHOLDER') }}', id), {
                    name: $(`#label_name_${id}`).val(),
                    sort: $(`#label_sort_${id}`).val()
                });
            }
        @endcan

        @can('admin.config.label.destroy')
            function delLabel(id, name) { // 删除标签
                confirmDelete(jsRoute('{{ route('admin.config.label.destroy', 'PLACEHOLDER') }}', id), name, '{{ trans('model.node.label') }}');
            }
        @endcan
    </script>
@endpush
