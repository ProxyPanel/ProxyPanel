@extends('admin.table_layouts')
@section('content')
    <div class="page-content container">
        <x-admin.table-panel title="<span class='cyan-600'><i class='icon wb-extension'></i>{{ trans('user.invite.attribute') }}</span>" :theads="[
            '#',
            trans('user.invite.attribute'),
            trans('common.available_date'),
            trans('user.inviter'),
            trans('common.status.attribute'),
            trans('user.invitee'),
        ]"
                             :count="trans('user.invite.counts', ['num' => $inviteList->total()])" :pagination="$inviteList->links()">
            <x-slot:actions>
                <div class="btn-group">
                    @can('admin.invite.create')
                        <button class="btn btn-primary btn-animate btn-animate-side" type="button" onclick="makeInvite()">
                            <i class="icon wb-plus"></i> {{ trans('common.generate_item', ['attribute' => trans('user.invite.attribute')]) }}
                        </button>
                    @endcan
                    <button class="btn btn-info" onclick="exportInvite()">{{ trans('admin.massive_export') }}</button>
                </div>
            </x-slot:actions>
            <x-slot:body>
                <x-alert type="info" :message="trans('user.invite.tips', ['num' => 10, 'days' => sysConfig('user_invite_days')])" />
            </x-slot:body>

            <x-slot:tbody>
                @foreach ($inviteList as $invite)
                    <tr>
                        <td> {{ $invite->id }} </td>
                        <td>
                            <a class="mt-clipboard" href="javascript:void(0)">{{ $invite->code }}</a>
                        </td>
                        <td> {{ $invite->dateline }} </td>
                        <td>
                            {{ $invite->inviter_id === null ? trans('admin.system_generate') : $invite->inviter->username ?? '【' . trans('common.deleted_item', ['attribute' => trans('common.account')]) . '】' }}
                        </td>
                        <td>
                            {!! $invite->status_label !!}
                        </td>
                        <td>
                            {{ $invite->status === 1 ? $invite->invitee->username ?? '【' . trans('common.deleted_item', ['attribute' => trans('common.account')]) . '】' : '' }}
                        </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
@push('javascript')
    <script>
        @can('admin.invite.create')
            // 生成邀请码
            function makeInvite() {
                ajaxPost('{{ route('admin.invite.create') }}');
            }
        @endcan

        @can('admin.invite.export')
            // 导出邀请码
            function exportInvite() {
                showConfirm({
                    title: '{{ trans('admin.hint') }}',
                    text: '{{ trans('admin.confirm.export') }}',
                    onConfirm: function() {
                        window.location.href = '{{ route('admin.invite.export') }}';
                    }
                });
            }
        @endcan

        $(document).on('click', '.mt-clipboard', function(e) {
            e.preventDefault();
            copyToClipboard(jsRoute('{{ route('register', ['code' => 'PLACEHOLDER']) }}', $(this).text()));
        });
    </script>
@endpush
