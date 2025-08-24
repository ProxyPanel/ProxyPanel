@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :theads="['#', trans('admin.user.group.name'), trans('common.action')]" :count="trans('admin.user.group.counts', ['num' => $groups->total()])" :pagination="$groups->links()" :title="trans('admin.menu.user.group') . ' <small>' . trans('admin.user.group.sub_title') . '</small>'"
                             :delete-config="['url' => route('admin.user.group.destroy', 'PLACEHOLDER'), 'attribute' => trans('model.user_group.attribute')]">
            <x-slot:actions>
                <a class="btn btn-primary" href="{{ route('admin.user.group.create') }}">
                    <i class="icon wb-plus" aria-hidden="true"></i> {{ trans('common.add') }}
                </a>
            </x-slot:actions>
            <x-slot:tbody>
                @foreach ($groups as $group)
                    <tr>
                        <td> {{ $group->id }} </td>
                        <td> {{ $group->name }} </td>
                        <td>
                            @canany(['admin.user.group.edit', 'admin.user.group.destroy'])
                                <div class="btn-group">
                                    @can('admin.user.group.edit')
                                        <a class="btn btn-primary" href="{{ route('admin.user.group.edit', $group) }}">
                                            <i class="icon wb-edit" aria-hidden="true"></i>
                                        </a>
                                    @endcan
                                    @can('admin.user.group.destroy')
                                        <button class="btn btn-danger" data-action="delete">
                                            <i class="icon wb-trash" aria-hidden="true"></i>
                                        </button>
                                    @endcan
                                </div>
                            @endcanany
                        </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
