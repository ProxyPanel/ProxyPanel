@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.shop.goods')" :theads="[
            '#',
            trans('model.goods.name'),
            trans('model.common.type'),
            trans('model.goods.logo'),
            trans('model.goods.traffic'),
            trans('model.goods.price'),
            trans('model.common.sort'),
            trans('admin.goods.sell_and_used'),
            trans('model.goods.hot'),
            trans('model.goods.limit_num'),
            trans('common.status.attribute'),
            trans('common.action'),
        ]" :count="trans('admin.goods.counts', ['num' => $goodsList->total()])" :pagination="$goodsList->links()" :delete-config="['url' => route('admin.goods.destroy', 'PLACEHOLDER'), 'attribute' => trans('model.goods.attribute')]">
            @can('admin.goods.create')
                <x-slot:actions>
                    <a class="btn btn-primary" href="{{ route('admin.goods.create') }}">
                        <i class="icon wb-plus"></i> {{ trans('common.add') }}
                    </a>
                </x-slot:actions>
            @endcan
            <x-slot:filters>
                <x-admin.filter.selectpicker class="col-lg-2 col-sm-4" name="type" :title="trans('model.common.type')" :options="[1 => trans('admin.goods.type.package'), 2 => trans('admin.goods.type.plan')]" />
                <x-admin.filter.selectpicker class="col-lg-2 col-sm-4" name="status" :title="trans('common.status.attribute')" :options="[1 => trans('admin.goods.status.yes'), 0 => trans('admin.goods.status.no')]" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($goodsList as $goods)
                    <tr>
                        <td> {{ $goods->id }} </td>
                        <td> {{ $goods->name }} </td>
                        <td>
                            @if ($goods->type === 1)
                                {{ trans('admin.goods.type.package') }}
                            @elseif($goods->type === 2)
                                {{ trans('admin.goods.type.plan') }}
                            @else
                                {{ trans('admin.goods.type.top_up') }}
                            @endif
                        </td>
                        <td style="background-color: {{ $goods->color ?? 'white' }}" @if ($goods->color) class="text-white" @endif>
                            @if ($goods->logo)
                                <a href="{{ asset($goods->logo) }}" target="_blank">
                                    <img class="h-50" src="{{ asset($goods->logo) }}" alt="logo" />
                                </a>
                            @elseif($goods->color)
                                {{ trans('common.none') }}
                            @endif
                        </td>
                        <td> {{ $goods->traffic_label }} </td>
                        <td> {{ $goods->price_tag }}</td>
                        <td> {{ $goods->sort }} </td>
                        <td><code>{{ $goods->use_count . ' / ' . $goods->total_count }}</code></td>
                        <td>
                            @if ($goods->is_hot)
                                🔥
                            @endif
                        </td>
                        <td>
                            {{ $goods->limit_num ?: trans('common.unlimited') }}
                        </td>
                        <td>
                            @if ($goods->status)
                                <span class="badge badge-lg badge-success">{{ trans('admin.goods.status.yes') }}</span>
                            @else
                                <span class="badge badge-lg badge-default">{{ trans('admin.goods.status.no') }}</span>
                            @endif
                        </td>
                        <td>
                            @canany(['admin.goods.edit', 'admin.goods.destroy'])
                                <div class="btn-group">
                                    @can('admin.goods.edit')
                                        <a class="btn btn-primary" href="{{ route('admin.goods.edit', $goods) }}">
                                            <i class="icon wb-edit"></i>
                                        </a>
                                    @endcan
                                    @can('admin.goods.destroy')
                                        <button class="btn btn-danger" data-action="delete">
                                            <i class="icon wb-trash"></i>
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
