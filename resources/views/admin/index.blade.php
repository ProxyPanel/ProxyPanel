@extends('admin.layouts')
@section('css')
    <link href="/assets/global/fonts/material-design/material-design.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="row" data-by-row="true">
            @can('admin.user.index')
                <x-admin.stats-card :href="route('admin.user.index')" icon="md-account" :title="trans('admin.dashboard.users')" :value="$totalUserCount" :tag="$todayRegister" />
                <x-admin.stats-card :href="route('admin.user.index', ['enable' => 1])" color="info" icon="md-account" :title="trans('admin.dashboard.available_users')" :value="$enableUserCount" />
                <x-admin.stats-card :href="route('admin.user.index', ['paying' => 1])" color="info" icon="md-money-box" :title="trans('admin.dashboard.paid_users')" :value="$payingUserCount" :tag="$payingNewUserCount" />
                <x-admin.stats-card :href="route('admin.user.index', ['active' => 1])" color="success" icon="md-account" :title="trans('admin.dashboard.active_days_users', ['days' => sysConfig('expire_days')])" :value="$activeUserCount" />
                <x-admin.stats-card :href="route('admin.user.index', ['unActive' => 1])" color="warning" icon="md-account" :title="trans('admin.dashboard.inactive_days_users', ['days' => sysConfig('expire_days')])" :value="$inactiveUserCount" />
                <x-admin.stats-card :href="route('admin.user.index', ['online' => 1])" color="success" icon="md-account" :title="trans('admin.dashboard.online_users')" :value="$onlineUserCount" />
                <x-admin.stats-card :href="route('admin.user.index', ['expireWarning' => 1])" color="danger" icon="md-account" :title="trans('admin.dashboard.expiring_users')" :value="$expireWarningUserCount" />
                <x-admin.stats-card :href="route('admin.user.index', ['largeTraffic' => 1])" color="warning" icon="md-account" :title="trans('admin.dashboard.overuse_users')" :value="$largeTrafficUserCount" />
                <x-admin.stats-card :href="route('admin.user.index', ['flowAbnormal' => 1])" color="danger" icon="md-account" :title="trans('admin.dashboard.abnormal_users')" :value="$flowAbnormalUserCount" />
            @endcan
            @can('admin.node.index')
                <x-admin.stats-card :href="route('admin.node.index')" icon="md-cloud" :title="trans('admin.dashboard.nodes')" :value="$nodeCount" />
                <x-admin.stats-card :href="route('admin.node.index', ['status' => 0])" color="info" icon="md-cloud-off" :title="trans('admin.dashboard.maintaining_nodes')" :value="$abnormalNodeCount" />
            @endcan
            @can('admin.log.traffic')
                <x-admin.stats-card :href="route('admin.report.siteAnalysis')" icon="md-time-countdown" :title="trans('admin.dashboard.days_traffic_consumed', ['days' => 30])" :value="$totalTrafficUsage" />
                <x-admin.stats-card :href="route('admin.log.traffic')" icon="md-time-countdown" :title="trans('admin.dashboard.current_month_traffic_consumed')" :value="$monthlyTrafficUsage" :tag="$dailyTrafficUsage" />
            @endcan
            @can('admin.order')
                <x-admin.stats-card :href="route('admin.order')" icon="md-ticket-star" :title="trans('admin.dashboard.orders')" :value="$totalOrder" :tag="$todayOrder" />
                <x-admin.stats-card :href="route('admin.order')" color="info" icon="md-ticket-star" :title="trans('admin.dashboard.online_orders')" :value="$totalOnlinePayOrder" :tag="$todayOnlinePayOrder" />
                <x-admin.stats-card :href="route('admin.order', ['status' => [1, 2]])" color="success" icon="md-ticket-star" :title="trans('admin.dashboard.succeed_orders')" :value="$totalSuccessOrder" :tag="$todaySuccessOrder" />
            @endcan
            @can('admin.log.credit')
                <x-admin.stats-card icon="md-money" :title="trans('admin.dashboard.credit')" :value="$totalCredit" />
            @endcan
            @can('admin.aff.rebate')
                <x-admin.stats-card :href="route('admin.aff.rebate')" color="warning" icon="md-money" :title="trans('admin.dashboard.withdrawing_commissions')" :value="$totalWaitRefAmount" :tag="$todayWaitRefAmount" />
            @endcan
            @can('admin.aff.index')
                <x-admin.stats-card color="dark" icon="md-money" :title="trans('admin.dashboard.withdrawn_commissions')" :value="$totalRefAmount" />
            @endcan
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/matchheight/jquery.matchHeight-min.js"></script>
    <script src="/assets/global/js/Plugin/matchheight.js"></script>
    <script>
        $(function() {
            $('.card').matchHeight();
        });
    </script>
@endsection
