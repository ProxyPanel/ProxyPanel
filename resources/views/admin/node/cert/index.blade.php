@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.node.cert')" :theads="[
            '#',
            trans('model.node_cert.domain'),
            trans('model.node_cert.key'),
            trans('model.node_cert.pem'),
            trans('model.node_cert.issuer'),
            trans('model.node_cert.signed_date'),
            trans('model.node_cert.expired_date'),
            trans('common.action'),
        ]" :count="trans('admin.node.cert.counts', ['num' => $certs->total()])" :pagination="$certs->links()" :delete-config="['url' => route('admin.node.cert.destroy', 'PLACEHOLDER'), 'attribute' => trans('model.node_cert.attribute')]">
            @can('admin.node.cert.create')
                <x-slot:actions>
                    <a class="btn btn-primary" href="{{ route('admin.node.cert.create') }}">
                        <i class="icon wb-plus" aria-hidden="true"></i> {{ trans('common.add') }}
                    </a>
                </x-slot:actions>
            @endcan
            <x-slot:tbody>
                @foreach ($certs as $cert)
                    <tr>
                        <td> {{ $cert->id }} </td>
                        <td> {{ $cert->domain }} </td>
                        <td> {{ $cert->key ? '✔️' : '❌' }} </td>
                        <td> {{ $cert->pem ? '✔️' : '❌' }} </td>
                        <td> {{ $cert->issuer }} </td>
                        <td> {{ $cert->from }} </td>
                        <td> {{ $cert->to }} </td>
                        <td>
                            @canany(['admin.node.cert.edit', 'admin.node.cert.destroy'])
                                <div class="btn-group">
                                    @can('admin.node.cert.edit')
                                        <a class="btn btn-primary" href="{{ route('admin.node.cert.edit', $cert) }}">
                                            <i class="icon wb-edit" aria-hidden="true"></i>
                                        </a>
                                    @endcan
                                    @can('admin.node.cert.destroy')
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
