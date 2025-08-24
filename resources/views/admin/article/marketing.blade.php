@extends('admin.table_layouts')
@push('css')
    <link href="/assets/global/vendor/summernote/summernote-bs4.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-tokenfield/bootstrap-tokenfield.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-markdown/bootstrap-markdown.min.css" rel="stylesheet">
@endpush
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.customer_service.marketing')" :theads="[
            '#',
            ucfirst(trans('validation.attributes.title')),
            trans('admin.marketing.send_status'),
            trans('admin.marketing.send_time'),
            trans('admin.marketing.error_message'),
            trans('common.action'),
        ]" :count="trans('admin.marketing.counts', ['num' => $marketingMessages->total()])" :pagination="$marketingMessages->links()">
            <x-slot:actions>
                @can('admin.marketing.email')
                    <button class="btn btn-primary" data-toggle="modal" data-target="#send_email_modal" type="button">
                        <i class="fa-solid fa-envelope"></i> {{ trans('admin.marketing.email_send') }}</button>
                @endcan
                @can('admin.marketing.push')
                    <button class="btn btn-primary" data-toggle="modal" data-target="#send_push_modal" type="button" disabled>
                        <i class="fa-solid fa-bell"></i> {{ trans('admin.marketing.push_send') }}</button>
                @endcan
            </x-slot:actions>
            <x-slot:filters>
                <x-admin.filter.selectpicker class="col-xxl-1 col-xl-2 col-lg-3 col-md-4 col-sm-6" name="status" :title="trans('common.status.attribute')" :options="[-1 => trans('common.failed'), 0 => trans('common.to_be_send'), 1 => trans('common.success')]" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($marketingMessages as $message)
                    <tr>
                        <td> {{ $message->id }} </td>
                        <td> {{ $message->title }} </td>
                        <td> {{ $message->status_label }} </td>
                        <td> {{ $message->created_at }} </td>
                        <td> {{ $message->error }} </td>
                        <td>
                            <a class="btn btn-primary" data-toggle="collapse" href="#marketing_{{ $loop->iteration }}" aria-expanded="false"
                               aria-controls="marketing_{{ $loop->iteration }}">{{ trans('common.view') }}</a>
                            @if ($message->type === 1)
                                <div class="collapse" id="marketing_{{ $loop->iteration }}">{!! $message->content !!}</div>
                            @else
                                <div class="collapse" id="marketing_{{ $loop->iteration }}">
                                    <div class="markdown-content" data-markdown="{{ $message->content }}" data-rendered="false"></div>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>

    @can('admin.marketing.email')
        <div class="modal fade" id="send_email_modal" data-focus-on="input:first" data-backdrop="static" data-keyboard="false" tabindex="-1">
            <div class="modal-dialog modal-lg modal-center">
                <div class="modal-content">
                    <div class="modal-header">
                        <button class="close" data-dismiss="modal" type="button" aria-label="{{ trans('common.close') }}">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title">
                            <i class="icon fa-solid fa-envelopes-bulk"></i>{{ trans('admin.marketing.email_send') }}
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <p class="font-size-18">
                                <i class="icon fa-solid fa-users-viewfinder"></i> {{ trans('admin.marketing.email.targeted_users_count') }}
                                <code class="ml-5" id="statistics"></code>
                            </p>
                        </div>
                        <h5>{{ trans('admin.marketing.email.filters') }}</h5>
                        <form class="form-row" id="filter-form">
                            <div class="form-group col-lg-3 col-md-4 col-6">
                                <input class="form-control" name="id" data-plugin="tokenfield" type="text" placeholder="{{ trans('model.user.id') }}" />
                            </div>
                            <div class="form-group col-lg-3 col-md-4 col-6">
                                <input class="form-control" name="username" data-plugin="tokenfield" type="text" placeholder="{{ trans('model.user.username') }}" />
                            </div>
                            <div class="form-group col-lg-3 col-md-4 col-6">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa-solid fa-calendar-minus"></i></span>
                                    </div>
                                    <input class="form-control" name="expire_start" data-plugin="datepicker" type="text"
                                           placeholder="{{ trans('admin.marketing.email.expire_start') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="form-group col-lg-3 col-md-4 col-6">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa-solid fa-calendar-plus"></i></span>
                                    </div>
                                    <input class="form-control" name="expire_end" data-plugin="datepicker" type="text"
                                           placeholder="{{ trans('admin.marketing.email.expire_end') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="form-group col-lg-3 col-md-4 col-6">
                                <div class="input-group">
                                    <input class="form-control" name="traffic" type="number" min="0" max="100"
                                           placeholder="{{ trans('admin.marketing.email.traffic_usage_over') }}" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-lg-3 col-md-4 col-6">
                                <div class="input-group">
                                    <input class="form-control" name="lastAlive" type="number" min="1"
                                           placeholder="{{ trans('admin.marketing.email.recently_active') }}" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">{{ ucfirst(trans('validation.attributes.minute')) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-auto">
                                <div class="checkbox-custom checkbox-primary">
                                    <input id="paying" name="paying" type="checkbox" />
                                    <label for="paying">{{ trans('admin.marketing.email.paid_servicing') }}</label>
                                </div>
                            </div>
                            <div class="form-group col-auto">
                                <div class="checkbox-custom checkbox-primary">
                                    <input id="notPaying" name="notPaying" type="checkbox" />
                                    <label for="notPaying">{{ trans('admin.marketing.email.previously_paid') }}</label>
                                </div>
                            </div>
                            <div class="form-group col-auto">
                                <div class="checkbox-custom checkbox-primary">
                                    <input id="paid" name="paid" type="checkbox" />
                                    <label for="paid">{{ trans('admin.marketing.email.ever_paid') }}</label>
                                </div>
                            </div>
                            <div class="form-group col-auto">
                                <div class="checkbox-custom checkbox-primary">
                                    <input id="neverPay" name="neverPay" type="checkbox" />
                                    <label for="neverPay">{{ trans('admin.marketing.email.never_paid') }}</label>
                                </div>
                            </div>
                            <div class="form-group col-auto">
                                <div class="checkbox-custom checkbox-primary">
                                    <input id="flowAbnormal" name="flowAbnormal" type="checkbox" />
                                    <label for="flowAbnormal">{{ trans('admin.marketing.email.recent_traffic_abnormal') }}</label>
                                </div>
                            </div>
                            @if ($userGroups)
                                <div class="form-group col-lg-3 col-md-4 col-6">
                                    <select class="form-control show-tick" name="user_group_id[]" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                            title="{{ trans('model.user_group.attribute') }}" multiple>
                                        @foreach ($userGroups as $key => $group)
                                            <option value="{{ $key }}">{{ $group }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            @if ($levels)
                                <div class="form-group col-lg-3 col-md-4 col-6">
                                    <select class="form-control show-tick" name="level[]" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                            title="{{ trans('model.common.level') }}" multiple>
                                        @foreach ($levels as $key => $level)
                                            <option value="{{ $key }}">{{ $level }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="form-group col-lg-3 col-md-4 col-6">
                                <select class="form-control show-tick" name="status[]" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                        title="{{ trans('model.user.account_status') }}" multiple>
                                    <option value="-1">{{ trans('common.status.banned') }}</option>
                                    <option value="0">{{ trans('common.status.inactive') }}</option>
                                    <option value="1">{{ trans('common.status.normal') }}</option>
                                </select>
                            </div>
                            <div class="form-group col-lg-3 col-md-4 col-6">
                                <select class="form-control" name="enable" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                        title="{{ trans('model.user.proxy_status') }}">
                                    <option value="1">{{ trans('common.status.enabled') }}</option>
                                    <option value="0">{{ trans('common.status.banned') }}</option>
                                </select>
                            </div>
                            <div class="form-group col-lg-3 col-md-4 col-6">
                                <button class="btn btn-primary" type="button" onclick="fetchStatistics()">{{ trans('admin.query') }}</button>
                                <button class="btn btn-danger" type="button" onclick="resetFilterForm()">{{ trans('common.reset') }}</button>
                            </div>
                        </form>
                        <div class="alert" id="msg" style="display: none;"></div>
                        <form class="form-horizontal" id="send-email-form">
                            <div class="form-body">
                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-md-1 control-label" for="title"> {{ ucfirst(trans('validation.attributes.title')) }} </label>
                                        <div class="col-md-8">
                                            <input class="form-control" id="title" name="title" type="text" />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-md-1 control-label" for="content"> {{ ucfirst(trans('validation.attributes.content')) }} </label>
                                        <div class="col-md-11">
                                            <textarea class="form-control" id="content" name="content"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger mr-auto" data-dismiss="modal">{{ trans('common.cancel') }}</button>
                        <button class="btn btn-primary" type="button" onclick="sendEmail()">{{ trans('common.send') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    @can('admin.marketing.push')
        <div class="modal fade" id="send_push_modal" data-focus-on="input:first" data-backdrop="static" data-keyboard="false" tabindex="-1">
            <div class="modal-dialog modal-lg modal-center">
                <div class="modal-content">
                    <div class="modal-header">
                        <button class="close" data-dismiss="modal" type="button" aria-label="{{ trans('common.close') }}">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title">{{ trans('admin.marketing.push_send') }}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" id="msg" style="display: none;"></div>
                        <form class="form-horizontal" action="#" method="post">
                            <div class="form-body">
                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-md-2 control-label" for="title"> {{ ucfirst(trans('validation.attributes.title')) }} </label>
                                        <div class="col-md-6">
                                            <input class="form-control" id="title" name="title" type="text" />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-md-2 control-label" for="content"> {{ ucfirst(trans('validation.attributes.content')) }} </label>
                                        <div class="col-md-9">
                                            <textarea class="form-control" id="content" name="content" data-provide="markdown" data-iconlibrary="fa" rows="10"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger mr-auto" data-dismiss="modal">{{ trans('common.cancel') }}</button>
                        <button class="btn btn-primary disabled" type="button" onclick="sendPush()">{{ trans('common.send') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection
@push('javascript')
    <script src="/assets/global/vendor/summernote/summernote-bs4.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    @if (app()->getLocale() !== 'en')
        <script src="/assets/global/vendor/summernote/lang/summernote-{{ config('common.language')[app()->getLocale()][2] }}.min.js"></script>
        <script src="/assets/global/vendor/bootstrap-datepicker/locales/bootstrap-datepicker.{{ str_replace('_', '-', app()->getLocale()) }}.min.js" charset="UTF-8">
        </script>
    @endif
    <script src="/assets/global/vendor/bootstrap-tokenfield/bootstrap-tokenfield.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-markdown/bootstrap-markdown.min.js"></script>
    <script src="/assets/global/vendor/marked/marked.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-tokenfield.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script>
        function renderMarkdown(element) {
            var markdownText = $(element).data("markdown");
            var htmlContent = marked(markdownText);
            $(element).html(htmlContent);
            $(element).data("rendered", true); // Mark as rendered
        }

        $(document).ready(function() {
            // Render Markdown content when collapse is shown
            $(".collapse").on("shown.bs.collapse", function() {
                if ($(this).find(".markdown-content").length > 0 && $(this).find(".markdown-content").data("rendered") === false) {
                    renderMarkdown($(this).find(".markdown-content"));
                }
            });

            @can('admin.marketing.email')
                fetchStatistics();

                $("#content").summernote({
                    tabsize: 2,
                    height: 400,
                    dialogsInBody: true,
                    lang: '{{ config('common.language')[app()->getLocale()][2] }}' // default: 'en-US'
                });
            @endcan
        });

        @can('admin.marketing.email')
            function resetFilterForm() {
                const form = $("#filter-form");
                form[0].reset();
                form.find("select").selectpicker("refresh");
                form.find("[data-plugin=\"tokenfield\"]").each(function() {
                    $(this).tokenfield("setTokens", []);
                });
                fetchStatistics();
            }

            function fetchStatistics() {
                ajaxGet('{{ route('admin.marketing.create', ['type' => 'email']) }}',
                    collectFormData("#filter-form", {
                        removeEmpty: true
                    }), {
                        beforeSend: function() {
                            $("#statistics").html('{{ trans('admin.marketing.email.loading_statistics') }}');
                        },
                        success: function(data) {
                            $("#statistics").html(data.count);
                        },
                        error: function() {
                            $("#statistics").html('<p>{{ trans('common.request_failed') }}</p>');
                        }
                    }
                );
            }

            function sendEmail() {
                const filterData = collectFormData("#filter-form", {
                    removeEmpty: true
                });
                const emailData = collectFormData("#send-email-form");

                ajaxPost(
                    '{{ route('admin.marketing.create', ['type' => 'email']) }}?' + $.param(filterData),
                    emailData, {
                        beforeSend: function() {
                            $("#msg")
                                .show()
                                .removeClass("alert-danger alert-success")
                                .addClass("alert-info")
                                .html('{{ trans('admin.creating') }}');
                        },
                        success: function(ret) {
                            $("#msg")
                                .show()
                                .removeClass("alert-info alert-danger alert-success")
                                .addClass(ret.status === "success" ? "alert-success" : "alert-danger")
                                .html(ret.message);
                        },
                        error: function() {
                            $("#msg")
                                .removeClass("alert-info alert-success")
                                .addClass("alert-danger")
                                .show()
                                .html('{{ trans('common.request_failed') }}');
                        }
                    }
                );
            }
        @endcan

        @can('admin.marketing.push')
            function sendPush() {
                ajaxPost(
                    '{{ route('admin.marketing.create', ['type' => 'push']) }}',
                    collectFormData("#send_push_modal form"), {
                        beforeSend: function() {
                            $("#msg").show().html('{{ trans('admin.creating') }}');
                        },
                        success: function(ret) {
                            if (ret.status === "fail") {
                                $("#msg").show().html(ret.message);
                                return;
                            }
                            $("#send_push_modal").modal("hide");
                        },
                        error: function() {
                            $("#msg").show().html('{{ trans('common.request_failed') }}');
                        }
                    }
                );
            }
        @endcan
    </script>
@endpush
