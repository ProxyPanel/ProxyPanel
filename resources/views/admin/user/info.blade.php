@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">
                    {{ isset($user) ? trans('admin.action.edit_item', ['attribute' => trans('model.user.attribute')]) : trans('admin.action.add_item', ['attribute' => trans('model.user.attribute')]) }}
                </h2>
                @isset($user)
                    @can('admin.user.switch')
                        <div class="panel-actions">
                            <button class="btn btn-sm btn-danger" type="button" onclick="switchToUser()">{{ trans('admin.user.info.switch') }}</button>
                        </div>
                    @endcan
                @endisset
            </div>
            <div class="panel-body">
                <form class="form-horizontal" onsubmit="return Submit()">
                    <div class="form-row">
                        <div class="col-lg-6">
                            <h4 class="example-title">{{ trans('admin.user.info.account') }}</h4>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="nickname">{{ trans('model.user.nickname') }}</label>
                                <div class="col-xl-6 col-sm-8">
                                    <input class="form-control" id="nickname" name="nickname" type="text" required />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="username">{{ trans('model.user.username') }}</label>
                                <div class="col-xl-6 col-sm-8">
                                    <input class="form-control" id="username" name="username" type="text" required />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="password">{{ trans('model.user.password') }}</label>
                                <div class="col-xl-6 col-sm-8">
                                    <input class="form-control" id="password" name="password" type="password" autocomplete="new-password"
                                           placeholder="@isset($user){{ trans('common.stay_unchanged') }} @else {{ trans('common.random_generate') }} @endisset" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="level">{{ trans('model.common.level') }}</label>
                                <div class="col-xl-4 col-sm-8">
                                    <select class="form-control" id="level" name="level" data-plugin="selectpicker" data-style="btn-outline btn-primary">
                                        @foreach ($levels as $level)
                                            <option value="{{ $level->level }}">{{ $level->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="group">{{ trans('model.user_group.attribute') }}</label>
                                <div class="col-xl-4 col-sm-8">
                                    <select class="form-control" id="group" name="group" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                            title="{{ trans('common.none') }}">
                                        @foreach ($userGroups as $group)
                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @isset($user)
                                <div class="form-group row">
                                    <label class="col-md-2 col-sm-3 col-form-label" for="credit">{{ trans('model.user.credit') }}</label>
                                    <div class="col-xl-4 col-sm-8">
                                        <div class="input-group">
                                            <p class="form-control"> {{ $user->credit }} </p>
                                            @can('admin.user.updateCredit')
                                                <div class="input-group-append">
                                                    <button class="btn btn-danger" data-toggle="modal" data-target="#handle_user_credit"
                                                            type="button">{{ trans('admin.goods.type.top_up') }}</button>
                                                </div>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            @endisset

                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="invite_num">{{ trans('model.user.invite_num') }}</label>
                                <div class="col-xl-4 col-sm-8">
                                    <input class="form-control" id="invite_num" name="invite_num" type="number" value="0" required />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="reset_time">{{ trans('model.user.reset_date') }}</label>
                                <div class="col-xl-4 col-sm-4">
                                    <div class="input-group input-daterange" data-plugin="datepicker">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="icon wb-calendar" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                        <input class="form-control" id="reset_time" name="reset_time" type="text" />
                                    </div>
                                    <span class="text-help"> {{ trans('admin.user.info.reset_date_hint') }} </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="expired_at">{{ trans('model.user.expired_date') }}</label>
                                <div class="col-xl-4 col-sm-4">
                                    <div class="input-group input-daterange" data-plugin="datepicker">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="icon wb-calendar" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                        <input class="form-control" id="expired_at" name="expired_at" type="text" />
                                    </div>
                                    <span class="text-help"> {{ trans('admin.user.info.expired_date_hint') }} </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label">{{ trans('model.user.account_status') }}</label>
                                <div class="col-md-10 col-sm-8">
                                    <ul class="list-unstyled list-inline">
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input id="normal" name="status" type="radio" value="1" checked />
                                                <label for="normal">{{ trans('common.status.normal') }}</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input id="nonactive" name="status" type="radio" value="0" />
                                                <label for="nonactive">{{ trans('common.status.inactive') }}</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input id="baned" name="status" type="radio" value="-1" />
                                                <label for="baned">{{ trans('common.status.banned') }}</label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="roles">{{ trans('model.user.role') }}</label>
                                <div class="col-xl-4 col-sm-8">
                                    <select class="form-control show-tick" id="roles" name="roles[]" data-plugin="selectpicker"
                                            data-style="btn-outline btn-primary" multiple>
                                        @foreach ($roles as $key => $description)
                                            <option value="{{ $key }}">{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="wechat">{{ trans('model.user.wechat') }}</label>
                                <div class="col-xl-6 col-sm-8">
                                    <input class="form-control" id="wechat" name="wechat" type="text" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="qq">{{ trans('model.user.qq') }}</label>
                                <div class="col-xl-6 col-sm-8">
                                    <input class="form-control" id="qq" name="qq" type="number" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="remark">{{ trans('model.user.remark') }}</label>
                                <div class="col-xl-6 col-sm-8">
                                    <textarea class="form-control" id="remark" name="remark" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <h4 class="example-title">{{ trans('admin.user.info.proxy') }}</h4>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="port">{{ trans('model.user.port') }}</label>
                                <div class="col-xl-5 col-sm-8">
                                    <div class="input-group">
                                        <input class="form-control" id="port" name="port" type="number"
                                               placeholder="{{ trans('common.random_generate') }}" />
                                        <div class="input-group-append">
                                            <button class="btn btn-success" type="button" onclick="makePort()">
                                                <i class="icon wb-refresh"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="uuid">{{ trans('model.user.uuid') }}</label>
                                <div class="col-xl-5 col-sm-8">
                                    <div class="input-group">
                                        <input class="form-control" id="uuid" name="uuid" type="text"
                                               placeholder="{{ trans('common.random_generate') }}" />
                                        <div class="input-group-append">
                                            <button class="btn btn-success" type="button" onclick="makeUUID()">
                                                <i class="icon wb-refresh"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <span class="text-help"> {{ trans('admin.user.info.uuid_hint') }} </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="passwd">{{ trans('model.user.proxy_passwd') }}</label>
                                <div class="col-xl-5 col-sm-8">
                                    <div class="input-group">
                                        <input class="form-control" id="passwd" name="passwd" type="text"
                                               placeholder="{{ trans('common.random_generate') }}" />
                                        <div class="input-group-append">
                                            <button class="btn btn-success" type="button" onclick="makePasswd()">
                                                <i class="icon wb-refresh"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="method">{{ trans('model.user.proxy_method') }}</label>
                                <div class="col-xl-5 col-sm-8">
                                    <select class="form-control" id="method" name="method" data-plugin="selectpicker" data-style="btn-outline btn-primary">
                                        @foreach (Helpers::methodList() as $method)
                                            <option value="{{ $method->name }}" @if ($method->is_default) selected @endif>{{ $method->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="transfer_enable">{{ trans('model.user.usable_traffic') }}</label>
                                <div class="col-xl-5 col-sm-8">
                                    <div class="input-group">
                                        <input class="form-control" id="transfer_enable" name="transfer_enable" type="text" value="1024" required>
                                        <span class="input-group-text">GB</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label">{{ trans('model.user.proxy_status') }}</label>
                                <div class="col-md-10 col-sm-8">
                                    <ul class="list-unstyled list-inline">
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input id="enable" name="enable" type="radio" value="1" checked />
                                                <label for="enable">{{ trans('common.status.enabled') }}</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input id="disable" name="enable" type="radio" value="0" />
                                                <label for="disable">{{ trans('common.status.banned') }}</label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="protocol">{{ trans('model.user.proxy_protocol') }}</label>
                                <div class="col-xl-5 col-sm-8">
                                    <select class="form-control" id="protocol" name="protocol" data-plugin="selectpicker"
                                            data-style="btn-outline btn-primary">
                                        @foreach (Helpers::protocolList() as $protocol)
                                            <option value="{{ $protocol->name }}" @if ($protocol->is_default) selected @endif>{{ $protocol->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="obfs">{{ trans('model.user.proxy_obfs') }}</label>
                                <div class="col-xl-5 col-sm-8">
                                    <select class="form-control" id="obfs" name="obfs" data-plugin="selectpicker" data-style="btn-outline btn-primary">
                                        @foreach (Helpers::obfsList() as $obfs)
                                            <option value="{{ $obfs->name }}" @if ($obfs->is_default) selected @endif>{{ $obfs->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <label class="col-md-2 col-sm-3 col-form-label" for="speed_limit">{{ trans('model.user.speed_limit') }}</label>
                                <div class="col-xl-5 col-sm-8">
                                    <div class="input-group">
                                        <input class="form-control" id="speed_limit" name="speed_limit" type="number" value="200" />
                                        <span class="input-group-text"> Mbps</span>
                                    </div>
                                    <span class="text-help">{{ trans('admin.zero_unlimited_hint') }} </span>
                                </div>
                            </div>
                            @isset($user)
                                <hr>
                                <div class="form-group row">
                                    <label class="col-md-2 col-sm-3 col-form-label" for="inviter">{{ trans('model.user.inviter') }}</label>
                                    <div class="col-xl-6 col-sm-8">
                                        <p class="form-control"> {{ $user->inviter->username ?? trans('common.none') }} </p>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2 col-sm-3 col-form-label" for="created_at">{{ trans('model.user.created_date') }}</label>
                                    <div class="col-xl-6 col-sm-8">
                                        <p class="form-control"> {{ $user->created_at }} </p>
                                    </div>
                                </div>
                            @endisset
                        </div>
                        <div class="col-12 form-actions text-right">
                            <a class="btn btn-secondary" href="{{ route('admin.user.index') }}">{{ trans('common.back') }}</a>
                            <button class="btn btn-success" type="submit">{{ trans('common.submit') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
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
                            <h4 class="modal-title">{{ trans('user.recharge') }}</h4>
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
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script>
        $(document).ready(function() {
            @isset($user)
                $('#nickname').val('{{ $user->nickname }}');
                $('#username').val('{{ $user->username }}');
                $('#level').selectpicker('val', '{{ $user->level }}');
                $('#group').selectpicker('val', '{{ $user->user_group_id }}');
                $('#invite_num').val('{{ $user->invite_num }}');
                $('#reset_time').val('{{ $user->reset_date }}');
                $('#expired_at').val('{{ $user->expiration_date }}');
                $("input[name='status'][value='{{ $user->status }}']").click();
                $('#wechat').val('{{ $user->wechat }}');
                $('#qq').val('{{ $user->qq }}');
                $('#remark').val('{{ $user->remark }}');
                $('#port').val('{{ $user->port }}');
                $('#passwd').val('{{ $user->passwd }}');
                $('#method').selectpicker('val', '{{ $user->method }}');
                $('#transfer_enable').val('{{ $user->transfer_enable / GiB }}');
                $("input[name='enable'][value='{{ $user->enable }}']").click();
                $('#protocol').selectpicker('val', '{{ $user->protocol }}');
                $('#obfs').selectpicker('val', '{{ $user->obfs }}');
                $('#speed_limit').val('{{ $user->speed_limit }}');
                $('#uuid').val('{{ $user->vmess_id }}');
                $('#roles').selectpicker('val', @json($user->roles()->pluck('name')));
            @else
                $('#level').selectpicker('val', '0');
            @endisset
        });

        $('.input-daterange>input').datepicker({
            format: 'yyyy-mm-dd',
        });

        @isset($user)
            @can('admin.user.switch')
                // 切换用户身份
                function switchToUser() {
                    $.ajax({
                        url: '{{ route('admin.user.switch', $user) }}',
                        data: {
                            '_token': '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        method: 'POST',
                        success: function(ret) {
                            if (ret.status === 'success') {
                                swal.fire({
                                    title: ret.message,
                                    icon: 'success',
                                    timer: 1000,
                                    showConfirmButton: false,
                                }).then(() => window.location.href = '/');
                            } else {
                                swal.fire({
                                    title: ret.message,
                                    icon: 'error'
                                }).then(() => window.location.reload());
                            }
                        },
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

                    $.ajax({
                        url: '{{ route('admin.user.updateCredit', $user) }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            amount: amount
                        },
                        beforeSend: function() {
                            $('#msg').show().html('{{ trans('user.recharging') }}');
                        },
                        success: function(ret) {
                            if (ret.status === 'fail') {
                                $('#msg').show().html(ret.message);
                                return false;
                            } else {
                                $('#handle_user_credit').modal('hide');
                                if (ret.status === 'success') {
                                    swal.fire({
                                        title: ret.message,
                                        icon: 'success',
                                        timer: 1000,
                                        showConfirmButton: false,
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    swal.fire({
                                        title: ret.message,
                                        icon: 'error'
                                    }).then(() => window.location.reload());
                                }
                            }
                        },
                        error: function() {
                            $('#msg').show().html('{{ trans('common.request_failed') }}');
                        },
                        complete: function() {},
                    });
                }
            @endcan
        @endisset

        // ajax同步提交
        function Submit() {
            // 用途
            let usage = '';
            $.each($('input:checkbox[name=\'usage\']'), function() {
                if (this.checked) {
                    usage += $(this).val() + ',';
                }
            });

            $.ajax({
                method: @isset($user)
                    'PUT'
                @else
                    'POST'
                @endisset ,
                url: '{{ isset($user) ? route('admin.user.update', $user) : route('admin.user.store') }}',
                dataType: 'json',
                data: {
                    _token: '{{ csrf_token() }}',
                    nickname: $('#nickname').val(),
                    username: $('#username').val(),
                    password: $('#password').val(),
                    port: $('#port').val(),
                    passwd: $('#passwd').val(),
                    uuid: $('#uuid').val(),
                    transfer_enable: $('#transfer_enable').val(),
                    enable: $('input:radio[name=\'enable\']:checked').val(),
                    method: $('#method option:selected').val(),
                    protocol: $('#protocol option:selected').val(),
                    obfs: $('#obfs option:selected').val(),
                    speed_limit: $('#speed_limit').val(),
                    wechat: $('#wechat').val(),
                    qq: $('#qq').val(),
                    expired_at: $('#expired_at').val(),
                    remark: $('#remark').val(),
                    level: $('#level').val(),
                    user_group_id: $('#group').val(),
                    roles: $('#roles').val(),
                    reset_time: $('#reset_time').val(),
                    invite_num: $('#invite_num').val(),
                    status: $('input:radio[name=\'status\']:checked').val(),
                },
                success: function(ret) {
                    if (ret.status === 'success') {
                        swal.fire({
                            title: '{{ trans('admin.hint') }}',
                            text: '{{ trans('admin.user.update_help') }}',
                            icon: 'question',
                            showCancelButton: true,
                            cancelButtonText: '{{ trans('common.close') }}',
                            confirmButtonText: '{{ trans('common.confirm') }}',
                        }).then((result) => {
                            if (result.value) {
                                window.location.href = '{!! route('admin.user.index') . (Request::getQueryString() ? '?' . Request::getQueryString() : '') !!}';
                            }
                        }, );
                    } else {
                        swal.fire({
                            title: ret.message,
                            icon: 'error',
                            timer: 1000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function(data) {
                    let str = '';
                    const errors = data.responseJSON;
                    if ($.isEmptyObject(errors) === false) {
                        $.each(errors.errors, function(index, value) {
                            str += '<li>' + value + '</li>';
                        });
                        swal.fire({
                            title: '{{ trans('admin.hint') }}',
                            html: str,
                            icon: 'error',
                            confirmButtonText: '{{ trans('common.confirm') }}',
                        });
                    }
                },
            });

            return false;
        }

        // 生成随机端口
        function makePort() {
            $.get('{{ route('getPort') }}', function(ret) {
                $('#port').val(ret);
            });
        }

        // 生成UUID
        function makeUUID() {
            $.get('{{ route('createUUID') }}', function(ret) {
                $('#uuid').val(ret);
            });
        }

        // 生成随机密码
        function makePasswd() {
            $.get('{{ route('createStr') }}', function(ret) {
                $('#passwd').val(ret);
            });
        }
    </script>
@endsection
