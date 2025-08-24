@extends('_layout')
@section('title', '订 阅')

@section('layout_css')
    <link href="/assets/global/fonts/font-awesome/css/all.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/toastr/toastr.min.css" rel="stylesheet">
    <link href="/assets/css/subscribe.css" rel="stylesheet">
@endsection
@section('body_class', 'layout-full position-relative')
@section('layout_content')
    <div class="container pt-15 pt-md-45 px-1 px-md-15">
        <div class="panel m-0">
            <!-- Top bar -->
            <div class="d-flex align-items-center justify-content-between mb-15">
                <h3 class="font-weight-bold text-white">{{ trans('model.subscribe.attribute') }}</h3>
                <div>
                    <button class="btn btn-outline-info btn-sm mt-clipboard btn-round-sm" data-clipboard-action="copy"><i
                           class="fas fa-copy mr-1"></i>{{ trans('user.subscribe.page.get_link') }}</button>
                    <!-- 语言选择下拉 -->
                    <div class="dropdown d-inline">
                        <button class="btn btn-outline-primary btn-sm dropdown-toggle btn-round-sm" id="langDropdown" data-toggle="dropdown" type="button"
                                aria-haspopup="true" aria-expanded="false">
                            <i class="fi fi-{{ config('common.language.' . app()->getLocale())[1] }}"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="langDropdown">
                            @foreach (config('common.language') as $key => $value)
                                @if ($key !== app()->getLocale())
                                    <a class="dropdown-item" href="{{ route('lang', ['locale' => $key]) }}">
                                        <i class="fi fi-{{ $value[1] }} mr-2" aria-hidden="true"></i>
                                        {{ $value[0] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <!-- 用户信息卡片 -->
            <div class="info-card mb-3">
                <div class="row align-items-center no-gutters">
                    <div class="col-auto text-center pr-0">
                        <img class="avatar-big d-block mx-auto rounded-circle" src="{{ $user->avatar }}" alt="avatar" />
                    </div>
                    <div class="col">
                        <div class="row text-center text-md-left pt-2 pt-md-0">
                            <div class="col-6 col-md-3 mb-2">
                                <div class="info-label"><i class="fa fa-user"></i> {{ trans('model.user.nickname') }}</div>
                                <div class="info-value">{{ $user->nickname }}</div>
                            </div>
                            <div class="col-6 col-md-3 mb-2">
                                <div class="info-label"><i class="fa fa-circle-dot text-success"></i> {{ trans('common.status.attribute') }}</div>
                                <div class="info-value text-success">
                                    @if ($user->enable)
                                        <div class="info-value text-success">
                                            <i class="fa fa-check mr-1"></i> {{ ucfirst(trans('common.status.enabled')) }}
                                        </div>
                                    @else
                                        <div class="info-value text-warning">
                                            <i class="fa fa-times mr-1"></i> {{ ucfirst(trans('common.status.disabled')) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-2">
                                <div class="info-label"><i class="fa-regular fa-clock text-warning"></i> {{ trans('model.user.expired_date') }}</div>
                                <div class="info-value">{{ localized_date($user->expiration_date) }}</div>
                            </div>
                            <div class="col-6 col-md-3 mb-2">
                                <div class="info-label"><i class="fa fa-gauge-high text-primary"></i> {{ trans('user.attribute.data') }}</div>
                                <div class="info-value text-info">{{ number_format($user->used_traffic / GiB, 2) }} / {{ $user->transfer_enable_formatted }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Installation -->
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h4 class="font-weight-semibold mb-0 text-white"><i class="fa fa-download text-primary mr-1"></i>{{ trans('user.tutorials') }}</h4>
                <ul class="nav nav-pills nav-platforms p-1 mb-0">
                    <li class="nav-item"><a class="nav-link" data-platform="pc" href="#"><i class="fa-solid fa-desktop mr-1"></i>PC</a></li>
                    <li class="nav-item"><a class="nav-link" data-platform="ios" href="#"><i class="fa-brands fa-apple mr-1"></i>iOS</a></li>
                    <li class="nav-item"><a class="nav-link" data-platform="android" href="#"><i class="fa-brands fa-android mr-1"></i>Android</a></li>
                </ul>
            </div>
            <div class="mb-3" id="apps-tab"></div>
            <!-- Step -->
            <div class="stepper" id="steps-container"></div>
        </div>
    </div>
@endsection
@section('layout_javascript')
    <script src="/assets/global/vendor/toastr/toastr.min.js"></script>
    <script src="/assets/global/js/Plugin/toastr.js"></script>
    <script src="/assets/custom/clipboardjs/clipboard.min.js"></script>

    <script>
        const createLocalizedGetter = (lang = "en") =>
            obj => obj?.[lang] ?? obj?.en;

        const trans = createLocalizedGetter(@json(app()->getLocale()));

        const sublink = @json($user->sub_url);
        const sublink_base64 = @json(base64url_encode($user->sub_url));
        let clients = {};
        const clipboard = new ClipboardJS(".mt-clipboard", {
            text: function() {
                return sublink;
            }
        });
        clipboard.on("success", function() {
            toastr.success('{{ trans('common.copy.success') }}');
        });
        clipboard.on("error", function() {
            toastr.error('{{ trans('common.copy.failed') }}');
        });

        async function loadClients() {
            try {
                const response = await fetch("/clients/app.json");
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return await response.json();
            } catch (error) {
                console.error("{{ trans('errors.report') }}", error);
                return {};
            }
        }

        loadClients().then(data => {
            clients = data;
            renderAppTabs();
            renderSteps();
        });

        let currentPlatform = "pc",
            currentAppIdx = 0;

        document.querySelectorAll(".nav-platforms .nav-link").forEach(function(tab, idx) {
            tab.onclick = function(e) {
                e.preventDefault();
                document.querySelectorAll(".nav-platforms .nav-link").forEach((el) => el.classList.remove("active"));
                tab.classList.add("active");
                currentPlatform = tab.dataset.platform;
                currentAppIdx = 0;
                renderAppTabs();
                renderSteps();
            };
            if (idx === 0) tab.classList.add("active");
        });

        function renderAppTabs() {
            const el = document.getElementById("apps-tab");
            el.innerHTML = "";
            const apps = clients[currentPlatform] || [];
            if (apps.length === 0) {
                el.innerHTML = "<span class=\"text-secondary\">{{ trans('user.subscribe.page.error.no_app') }}</span>";
                document.getElementById("steps-container").innerHTML = "";
                return;
            }
            apps.forEach(function(app, i) {
                const b = document.createElement("button");
                b.className = "btn btn-app btn-sm mr-1 mb-1" + (i === currentAppIdx ? " selected" : "");
                b.innerHTML = (app.isFeatured ? "<i class=\"fa fa-star text-warning\"></i> " : "") + app.name;
                b.onclick = function() {
                    currentAppIdx = i;
                    renderAppTabs();
                    renderSteps();
                };
                el.appendChild(b);
            });
        }

        function renderSteps() {
            const app = (clients[currentPlatform] || [])[currentAppIdx];
            if (!app) {
                document.getElementById("steps-container").innerHTML = "";
                return;
            }

            const steps = [];
            if (app.installationStep) {
                steps.push({
                    dot: "install",
                    title: `{{ trans('common.download_item', ['attribute' => '${app.name}']) }}`,
                    desc: trans(app.installationStep.description),
                    btns: app.installationStep.buttons?.map((btn) => ({
                        url: btn.buttonLink,
                        txt: trans(btn.buttonText)
                    })),
                    ic: "fa fa-download"
                });
            }

            if (app.additionalBeforeAddSubscriptionStep) {
                steps.push({
                    dot: "simple",
                    title: trans(app.additionalBeforeAddSubscriptionStep.title),
                    desc: trans(app.additionalBeforeAddSubscriptionStep.description),
                    ic: "fa fa-language"
                });
            }

            if (app.addSubscriptionStep) {
                steps.push({
                    dot: "sub",
                    title: "{{ trans('admin.action.add_item', ['attribute' => trans('model.subscribe.attribute')]) }}",
                    desc: trans(app.addSubscriptionStep.description),
                    addSubBtn: app.urlScheme + (app.isNeedBase64Encoding ? sublink_base64 : sublink),
                    ic: "fa fa-plus"
                });
            }

            if (app.additionalAfterAddSubscriptionStep) {
                steps.push({
                    dot: "simple",
                    title: trans(app.additionalAfterAddSubscriptionStep.title),
                    desc: trans(app.additionalAfterAddSubscriptionStep.description),
                    ic: "fa fa-comment"
                });
            }

            if (app.connectAndUseStep) {
                steps.push({
                    dot: "last",
                    title: "{{ trans('user.subscribe.page.connect') }}",
                    desc: trans(app.connectAndUseStep.description),
                    ic: "fa fa-bolt"
                });
            }

            let html = "";
            steps.forEach((step, idx) => {
                html += `
          <div class="step-item step-${step.dot}">
            <div class="step-dot"><i class="${step.ic || "fa fa-circle"}"></i></div>
            <div class="step-content">
              ${step.title ? `<div class="step-title text-white">${step.title || ""}</div>` : ""}
              ${step.desc ? `<div class="step-desc">${step.desc}</div>` : ""}
              ${step.btns && step.btns.length ? `<div class="btn-group flex-wrap mb-2 app-download">${step.btns.map((btn) => `<a href="${btn.url}" target="_blank" class="btn btn-outline-info btn-sm mr-2 mb-1">${btn.txt}</a>`).join("")}</div>` : ""}
              ${step.addSubBtn ? `<a class="btn addsub-btn" href="${step.addSubBtn}"><i class="fa fa-plus mr-2"></i>{{ trans('admin.action.add_item', ['attribute' => trans('model.subscribe.attribute')]) }}</a>` : ""}
            </div>
          </div>
        `;
            });

            document.getElementById("steps-container").innerHTML = html;
        }
    </script>
@endsection
