@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.log.notify')" :theads="[
            '#',
            trans('model.common.type'),
            trans('model.notification.address'),
            ucfirst(trans('validation.attributes.title')),
            ucfirst(trans('validation.attributes.content')),
            trans('model.notification.created_at'),
            trans('model.notification.status'),
        ]" :count="trans('admin.logs.counts', ['num' => $notificationLogs->total()])" :pagination="$notificationLogs->links()">
            <x-slot:filters>
                <x-admin.filter.input class="col-lg-3 col-sm-4" name="username" :placeholder="trans('common.account')" />
                <x-admin.filter.selectpicker class="col-lg-2 col-sm-4" name="type" :title="trans('model.common.type')" :options="collect(config('common.notification.labels'))->mapWithKeys(
                    fn($value, $key) => [$key => trans('admin.system.notification.channel.' . $value)],
                )" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($notificationLogs as $log)
                    <tr>
                        <td> {{ $log->id }} </td>
                        <td> {{ $log->type_label }} </td>
                        <td> {{ $log->address }} </td>
                        <td> {{ $log->title }} </td>
                        <td class="text-break"> {{ $log->content }} </td>
                        <td> {{ $log->created_at }} </td>
                        <td>
                            @if ($log->status < 0)
                                <p class="badge badge-danger text-break font-size-14"> {{ $log->error }} </p>
                            @elseif($log->status > 0)
                                <labe class="badge badge-success">{{ trans('common.success') }}</labe>
                            @else
                                <span class="badge badge-default"> {{ trans('common.status.pending_dispatch') }} </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
