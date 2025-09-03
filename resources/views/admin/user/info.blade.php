@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <x-ui.panel icon="wb-user-add" :title="trans(isset($user) ? 'admin.action.edit_item' : 'admin.action.add_item', ['attribute' => trans('model.user.attribute')])">
            @isset($user)
                @can('admin.user.switch')
                    <x-slot:actions>
                        <button class="btn btn-sm btn-danger" type="button" onclick="switchToUser()">{{ trans('admin.user.info.switch') }}</button>
                    </x-slot:actions>
                @endcan
            @endisset
            <x-admin.form.container handler="Submit()">
                <div class="form-row">
                    <div class="col-lg-6">
                        <h4 class="example-title">{{ trans('admin.user.info.account') }}</h4>
                        <x-admin.form.input name="nickname" :label="trans('model.user.nickname')" required />
                        <x-admin.form.input name="username" :label="trans('model.user.username')" required />
                        <x-admin.form.input name="password" type="password" :label="trans('model.user.password')" :placeholder="isset($user) ? trans('common.stay_unchanged') : trans('common.random_generate')" attribute="autocomplete=new-password" />
                        <x-admin.form.select name="level" :label="trans('model.common.level')" :options="$levels" />
                        <x-admin.form.select name="user_group_id" :label="trans('model.user_group.attribute')" :options="$userGroups" :placeholder="trans('common.none')" />
                        @isset($user)
                            <x-admin.form.skeleton name="credit" :label="trans('model.user.credit')">
                                <div class="input-group">
                                    <p class="form-control"> {{ $user->credit }} </p>
                                    @can('admin.user.updateCredit')
                                        <div class="input-group-append">
                                            <button class="btn btn-danger" data-toggle="modal" data-target="#handle_user_credit" type="button">
                                                {{ trans('user.recharge') }}
                                            </button>
                                        </div>
                                    @endcan
                                </div>
                            </x-admin.form.skeleton>
                        @endisset
                        <x-admin.form.input name="invite_num" type="number" :label="trans('model.user.invite_num')" input_grid="col-auto" required />
                        <x-admin.form.input-group name="reset_time" attribute="data-plugin=datepicker" :label="trans('model.user.reset_date')" :prependIcon="'icon wb-calendar'" :help="trans('admin.user.info.reset_date_hint')"
                                                  input_grid="col-auto" />
                        <x-admin.form.input-group name="expired_at" attribute="data-plugin=datepicker" :label="trans('model.user.expired_date')" :prependIcon="'icon wb-calendar'" :help="trans('admin.user.info.expired_date_hint')"
                                                  input_grid="col-auto" />
                        <x-admin.form.radio-group name="status" :label="trans('model.user.account_status')" :options="[-1 => trans('common.status.banned'), 0 => trans('common.status.inactive'), 1 => trans('common.status.normal')]" />
                        <x-admin.form.select name="roles" :label="trans('model.user.role')" :options="$roles" multiple="true"
                                             input_grid="col-xxl-4 col-xl-6 col-lg-8 col-md-6 col-sm-8" />
                        <x-admin.form.input name="wechat" :label="trans('model.user.wechat')" />
                        <x-admin.form.input name="qq" :label="trans('model.user.qq')" />
                        <x-admin.form.textarea name="remark" :label="trans('model.user.remark')" />
                    </div>
                    <div class="col-lg-6">
                        <h4 class="example-title">{{ trans('admin.user.info.proxy') }}</h4>
                        <x-admin.form.input-group name="port" type="number" :placeholder="trans('common.random_generate')" :label="trans('model.user.port')" button='<i class="icon wb-refresh"></i>'
                                                  buttonClass="btn-success" buttonOnclick="makePort()" />
                        <x-admin.form.input-group name="vmess_id" :placeholder="trans('common.random_generate')" :label="trans('model.user.uuid')" button='<i class="icon wb-refresh"></i>'
                                                  buttonClass="btn-success" buttonOnclick="makeUUID()" :help="trans('admin.user.info.uuid_hint')" />
                        <x-admin.form.input-group name="passwd" :placeholder="trans('common.random_generate')" :label="trans('model.user.proxy_passwd')" button='<i class="icon wb-refresh"></i>'
                                                  buttonClass="btn-success" buttonOnclick="makePasswd()" />
                        <x-admin.form.input-group name="transfer_enable" :label="trans('model.user.usable_traffic')" required append="GB" />
                        <x-admin.form.radio-group name="enable" :label="trans('model.user.proxy_status')" :options="[0 => trans('common.status.banned'), 1 => trans('common.status.enabled')]" />
                        <hr>
                        <x-admin.form.select name="method" :label="trans('model.user.proxy_method')" :options="$methods" input_grid="col-xxl-3 col-xl-5 col-lg-6 col-md-4 col-sm-auto" />
                        <x-admin.form.select name="protocol" :label="trans('model.user.proxy_protocol')" :options="$protocols" input_grid="col-xxl-3 col-xl-5 col-lg-6 col-md-4 col-sm-auto" />
                        <x-admin.form.select name="obfs" :label="trans('model.user.proxy_obfs')" :options="$obfs" input_grid="col-xxl-3 col-xl-5 col-lg-6 col-md-4 col-sm-auto" />
                        <hr />
                        <x-admin.form.input-group name="speed_limit" type="number" :label="trans('model.user.speed_limit')" append="Mbps" :help="trans('admin.zero_unlimited_hint')" />
                        @isset($user)
                            <hr />
                            <x-admin.form.skeleton name="inviter" :label="trans('model.user.inviter')">
                                <p class="form-control"> {{ $user->inviter->username ?? trans('common.none') }} </p>
                            </x-admin.form.skeleton>
                            <x-admin.form.skeleton name="created_at" :label="trans('model.user.created_date')">
                                <p class="form-control"> {{ localized_date($user->created_at) }} </p>
                            </x-admin.form.skeleton>
                        @endisset
                        <div class="col-12 form-actions text-right">
                            <a class="btn btn-secondary" href="{{ route('admin.user.index') }}">{{ trans('common.back') }}</a>
                            <button class="btn btn-success" type="submit">{{ trans('common.submit') }}</button>
                        </div>
                    </div>
                </div>
            </x-admin.form.container>
        </x-ui.panel>
    </div>
    @isset($user)
        @can('admin.user.updateCredit')
            <!-- 余额充值 -->
            <div class="modal fade" id="handle_user_credit" role="dialog" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog modal-simple modal-center">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button class="close" data-dismiss="modal" type="button" aria-label="{{ trans('common.close') }}">
                                <span aria-hidden="true">×</span>
                            </button>
                            <h4 class="modal-title">{{ trans('admin.goods.type.top_up') }}</h4>
                        </div>
                        <form class="modal-body" method="post">
                            <div class="alert alert-danger" id="msg" style="display: none;"></div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="amount"> {{ trans('user.shop.change_amount') }} </label>
                                <input class="col-sm-4 form-control" id="amount" name="amount" type="number"
                                       placeholder="{{ trans('admin.user.info.recharge_placeholder') }}" step="0.01" />
                            </div>
                        </form>
                        <div class="modal-footer">
                            <button class="btn btn-danger mr-auto" data-dismiss="modal">{{ trans('common.close') }}</button>
                            <button class="btn btn-primary" type="button" onclick="handleUserCredit()">{{ trans('user.recharge') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    @endisset
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    @if (app()->getLocale() !== 'en')
        <script src="/assets/global/vendor/bootstrap-datepicker/locales/bootstrap-datepicker.{{ str_replace('_', '-', app()->getLocale()) }}.min.js" charset="UTF-8">
        </script>
    @endif
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script>
        $(document).ready(function() {
            let userData = { // 默认值
                level: 0,
                invite_num: 0,
                status: 1,
                transfer_enable: 1024,
                enable: 1,
                method: '{{ $methodDefault }}',
                protocol: '{{ $protocolDefault }}',
                obfs: '{{ $obfsDefault }}',
                speed_limit: 200
            }
            @isset($user)
                // 预处理需要特殊处理的字段
                userData = {
                    ...@json($user),
                    expired_at: '{{ $user->expiration_date }}',
                    transfer_enable: '{{ $user->transfer_enable / GiB }}',
                    reset_time: '{{ $user->reset_date }}',
                    roles: @json($user->roles()->pluck('name')),
                }
            @endisset

            // 自动填充表单
            autoPopulateForm(userData, {
                skipFields: 'password'
            });
        });

        @isset($user)
            @can('admin.user.switch')
                // 切换用户身份
                function switchToUser() {
                    ajaxPost('{{ route('admin.user.switch', $user) }}', {}, {
                        success: function(ret) {
                            handleResponse(ret, {
                                reload: ret.status === 'success',
                                redirectUrl: ret.status === 'success' ? '/' : null
                            });
                        }
                    });
                }
            @endcan

            @can('admin.user.updateCredit')
                // 余额充值
                function handleUserCredit() {
                    const amount = $('#amount').val();
                    const reg = /^(-?)\d+(\.\d+)?$/; //只可以是正负数字

                    if (amount.trim() === '' || amount === 0 || !reg.test(amount)) {
                        $('#msg').show().html('{{ trans('user.shop.change_amount_help') }}');
                        $('#name').focus();
                        return false;
                    }

                    ajaxPost('{{ route('admin.user.updateCredit', $user) }}', {
                        amount: amount
                    }, {
                        beforeSend: function() {
                            $('#msg').show().html('{{ trans('user.recharging') }}');
                        },
                        success: function(ret) {
                            if (ret.status === 'fail') {
                                $('#msg').show().html(ret.message);
                                return false;
                            } else {
                                $('#handle_user_credit').modal('hide');
                                handleResponse(ret, {
                                    reload: true
                                });
                            }
                        },
                        error: function() {
                            $('#msg').show().html('{{ trans('common.request_failed') }}');
                        }
                    });
                }
            @endcan
        @endisset


        // 使用表单提交函数
        function Submit() {
            ajaxRequest({
                url: '{{ isset($user) ? route('admin.user.update', $user) : route('admin.user.store') }}',
                method: '{{ isset($user) ? 'PUT' : 'POST' }}',
                data: collectFormData('.form-horizontal'),
                success: function(ret) {
                    handleResponse(ret, {
                        redirectUrl: '{{ route('admin.user.index') . (Request::getQueryString() ? '?' . Request::getQueryString() : '') }}'
                    });
                },
                error: function(xhr) {
                    handleErrors(xhr, {
                        form: '.form-horizontal'
                    });
                }
            });

            return false;
        }

        // 生成随机端口
        function makePort() {
            ajaxGet('{{ route('getPort') }}', {}, {
                success: function(ret) {
                    $('#port').val(ret);
                }
            });
        }

        // 生成UUID
        function makeUUID() {
            ajaxGet('{{ route('createUUID') }}', {}, {
                success: function(ret) {
                    $('#vmess_id').val(ret);
                }
            });
        }

        // 生成随机密码
        function makePasswd() {
            ajaxGet('{{ route('createStr') }}', {}, {
                success: function(ret) {
                    $('#passwd').val(ret);
                }
            });
        }
    </script>
@endsection
