<div class="nav-tabs-horizontal" data-plugin="tabs">
    <ul class="nav nav-tabs nav-tabs-line mr-25" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" data-toggle="tab" href="#android_client" aria-controls="android_client" role="tab" aria-expanded="true">
                <i class="icon fa-android" aria-hidden="true"></i>Android</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" data-toggle="tab" href="#ios_client" aria-controls="quantumult_client" role="tab">
                <i class="icon fa-apple" aria-hidden="true"></i>iOS<span class="badge badge-danger up">{{trans('common.advance')}}</span></a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" data-toggle="tab" href="#windows_client" aria-controls="windows_client" role="tab">
                <i class="icon fa-windows" aria-hidden="true"></i>Windows</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" data-toggle="tab" href="#game_client" aria-controls="game_client_sstap" role="tab">
                <i class="icon fa-windows" aria-hidden="true"></i>Windows<span class="badge badge-info up">{{trans('common.recommend')}}</span></a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" data-toggle="tab" href="#linux_client" aria-controls="game_client_netch" role="tab">
                <i class="icon fa-windows" aria-hidden="true"></i>Linux</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" data-toggle="tab" href="#mac_client" aria-controls="macos_client" role="tab">
                <i class="icon fa-apple" aria-hidden="true"></i>Mac</a>
        </li>
    </ul>
    <div class="tab-content py-15">
        <div class="tab-pane active" id="android_client" role="tabpanel">
            @include('user.components.help.clients.android')
        </div>
        <div class="tab-pane" id="ios_client" role="tabpanel">
            @include('user.components.help.clients.ios')
        </div>
        <div class="tab-pane" id="windows_client" role="tabpanel">
            @include('user.components.help.clients.windows')
        </div>
        <div class="tab-pane" id="game_client" role="tabpanel">
            @include('user.components.help.clients.game')
        </div>
        <div class="tab-pane" id="linux_client" role="tabpanel">
            @include('user.components.help.clients.linux')
        </div>
        <div class="tab-pane" id="mac_client" role="tabpanel">
            @include('user.components.help.clients.mac')
        </div>
    </div>
</div>