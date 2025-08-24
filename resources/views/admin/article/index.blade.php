@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.customer_service.article')" :theads="[
            '#',
            trans('model.common.type'),
            trans('model.article.category'),
            ucfirst(trans('validation.attributes.title')),
            trans('model.article.language'),
            trans('model.common.sort'),
            trans('model.article.created_at'),
            trans('common.action'),
        ]" :count="trans('admin.article.counts', ['num' => $articles->total()])" :pagination="$articles->links()" :delete-config="['url' => route('admin.article.destroy', 'PLACEHOLDER'), 'attribute' => trans('model.article.attribute'), 'nameColumn' => 3]">
            @can('admin.article.create')
                <x-slot:actions>
                    <a class="btn btn-primary" href="{{ route('admin.article.create') }}">
                        <i class="icon wb-plus" aria-hidden="true"></i> {{ trans('common.add') }}
                    </a>
                </x-slot:actions>
            @endcan
            <x-slot:filters>
                <x-admin.filter.input class="col-md-1 col-sm-4" name="id" type="number" placeholder="ID" />
                <x-admin.filter.selectpicker class="col-xxl-1 col-lg-2 col-md-3 col-4" name="type" :title="trans('model.common.type')" :options="[1 => trans('admin.article.type.knowledge'), 2 => trans('admin.article.type.announcement')]" />
                <x-admin.filter.selectpicker class="col-xxl-1 col-md-3 col-4" name="category" :title="trans('model.article.category')" :options="$categories" />
                <x-admin.filter.selectpicker class="col-xxl-1 col-lg-2 col-md-3 col-4" name="language" :title="trans('model.article.language')">
                    @foreach (config('common.language') as $key => $value)
                        <option data-content="<i class='fi fi-{{ $value[1] }} mr-5' aria-hidden='true'></i> {{ $value[0] }}" value="{{ $key }}">
                        </option>
                    @endforeach
                </x-admin.filter.selectpicker>
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($articles as $article)
                    <tr>
                        <td> {{ $article->id }} </td>
                        @if ($article->type === 1)
                            <td> {{ trans('admin.article.type.knowledge') }}</td>
                        @elseif ($article->type === 2)
                            <td> {{ trans('admin.article.type.announcement') }}</td>
                        @else
                            <td> {{ trans('common.status.unknown') }}</td>
                        @endif
                        <td class="text-left">
                            @if ($article->category)
                                {{ Str::limit($article->category, 30) }}
                            @endif
                        </td>
                        <td class="text-left">
                            @if ($article->logo)
                                <img class="mr-5" src="{{ asset($article->logo) }}" alt="logo" style="height: 32px" loading="lazy" />
                            @endif
                            @if ($article->title)
                                {{ Str::limit($article->title, 50) }}
                            @endif
                        </td>
                        <td>
                            @if (isset(config('common.language')[$article->language]))
                                <i class="fi fis fi-{{ config('common.language')[$article->language][1] }}" aria-hidden="true"></i>
                                <span style="padding: inherit;">{{ config('common.language')[$article->language][0] }}</span>
                            @else
                                {{ __('common.status.unknown') }}
                            @endif
                        <td> {{ $article->sort }} </td>
                        <td> {{ $article->created_at }} </td>
                        <td>
                            @canany(['admin.article.show', 'admin.article.edit', 'admin.article.destroy'])
                                <div class="btn-group">
                                    @can('admin.article.show')
                                        <a class="btn btn-outline-success" href="{{ route('admin.article.show', $article) }}">
                                            <i class="icon wb-eye" aria-hidden="true"></i></a>
                                    @endcan
                                    @can('admin.article.edit')
                                        <a class="btn btn-outline-primary" href="{{ route('admin.article.edit', ['article' => $article->id]) }}">
                                            <i class="icon wb-edit" aria-hidden="true"></i></a>
                                    @endcan
                                    @can('admin.article.destroy')
                                        <a class="btn btn-outline-danger" data-action="delete" href="javascript:(0)">
                                            <i class="icon wb-close" aria-hidden="true"></i></a>
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
