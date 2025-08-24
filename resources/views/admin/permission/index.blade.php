@extends('admin.table_layouts')
@section('content')
    <div class="page-content container">
        <x-admin.table-panel :title="trans('admin.menu.rbac.permission')" :theads="['#', trans('model.permission.description'), trans('model.permission.name'), trans('common.action')]" :count="trans('admin.permission.counts', ['num' => $permissions->total()])" :pagination="$permissions->links()" :delete-config="['url' => route('admin.permission.destroy', 'PLACEHOLDER'), 'attribute' => trans('model.permission.attribute')]">
            @can('admin.permission.create')
                <x-slot:actions>
                    <a class="btn btn-outline-primary" href="{{ route('admin.permission.create') }}">
                        <i class="icon wb-plus" aria-hidden="true"></i> {{ trans('common.add') }}
                    </a>
                </x-slot:actions>
            @endcan
            <x-slot:filters>
                <x-admin.filter.input class="col-lg-5 col-sm-6" name="description" :placeholder="trans('model.permission.description')" />
                <x-admin.filter.input class="col-lg-5 col-sm-6" name="name" :placeholder="trans('model.permission.name')" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($permissions as $permission)
                    <tr>
                        <td>{{ $permission->id }}</td>
                        <td>{{ $permission->description }}</td>
                        <td>{{ $permission->name }}</td>
                        <td>
                            @canany(['admin.permission.edit', 'admin.permission.destroy'])
                                <div class="btn-group">
                                    @can('admin.permission.edit')
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.permission.edit', $permission) }}">
                                            <i class="icon wb-edit"></i></a>
                                    @endcan
                                    @can('admin.permission.destroy')
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
