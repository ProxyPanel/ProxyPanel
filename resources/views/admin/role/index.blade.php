@extends('admin.table_layouts')
@section('content')
    <div class="page-content container">
        <x-admin.table-panel :title="trans('admin.menu.rbac.role')" :theads="['#', trans('model.role.name'), trans('model.role.permissions'), trans('common.action')]" :count="trans('admin.role.counts', ['num' => $roles->total()])" :pagination="$roles->links()" :delete-config="['url' => route('admin.role.destroy', 'PLACEHOLDER'), 'attribute' => trans('model.role.attribute')]">
            @can('admin.role.create')
                <x-slot:actions>
                    <a class="btn btn-outline-primary" href="{{ route('admin.role.create') }}">
                        <i class="icon wb-plus" aria-hidden="true"></i> {{ trans('common.add') }}
                    </a>
                </x-slot:actions>
            @endcan
            <x-slot:tbody>
                @foreach ($roles as $role)
                    <tr>
                        <td>{{ $role->id }}</td>
                        <td>{{ $role->description }}</td>
                        <td>
                            @if ($role->name === 'Super Admin')
                                <span class="badge badge-info">{{ trans('admin.role.permissions_all') }}</span>
                            @else
                                @foreach ($role->permission_descriptions as $description)
                                    <span class="badge badge-info">{{ $description }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td>
                            @canany(['admin.role.edit', 'admin.role.destroy'])
                                <div class="btn-group">
                                    @can('admin.role.edit')
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.role.edit', $role) }}">
                                            <i class="icon wb-edit"></i></a>
                                    @endcan
                                    @can('admin.role.destroy')
                                        <button class="btn btn-sm btn-outline-danger" data-action="delete">
                                            <i class="icon wb-trash"></i></button>
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
