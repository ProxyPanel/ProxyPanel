@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.user.oauth')" :theads="['#', trans('model.user.attribute'), trans('model.oauth.type'), trans('model.oauth.identifier'), trans('common.action')]" :count="trans('admin.oauth.counts', ['num' => $list->total()])" :pagination="$list->links()">
            <x-slot:filters>
                <x-admin.filter.input class="col-xxl-2 col-md-3 col-sm-4" name="username" :placeholder="trans('model.user.username')" />
                <x-admin.filter.selectpicker class="col-xxl-2 col-md-3 col-sm-4" name="type" :title="trans('model.oauth.type')" :options="config('common.oauth.labels')" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($list as $item)
                    <tr>
                        <td> {{ $item->id }} </td>
                        <td> {{ $item->user->username ?? $item->user->id }} </td>
                        <td> {{ config('common.oauth.labels')[$item->type] ?? $item->type }} </td>
                        <td> {{ $item->identifier }} </td>
                        <td></td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
