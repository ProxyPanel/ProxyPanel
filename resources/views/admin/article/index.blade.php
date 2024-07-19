@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('admin.article.title') }}</h3>
                @can('admin.article.create')
                    <div class="panel-actions">
                        <a class="btn btn-primary" href="{{ route('admin.article.create') }}">
                            <i class="icon wb-plus" aria-hidden="true"></i> {{ trans('common.add') }}
                        </a>
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-xxl-1 col-lg-1 col-md-1 col-sm-4">
                        <input class="form-control" type="number" value="{{ Request::query('id') }}" placeholder="ID" />
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" id="type" name="type" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                title="{{ trans('model.common.type') }}">
                            <option value="1">{{ trans('admin.article.type.knowledge') }}</option>
                            <option value="2">{{ trans('admin.article.type.announcement') }}</option>
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" id="category" name="category" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                title="{{ trans('model.article.category') }}">
                            @foreach ($categories as $category)
                                <option value="{{ $category->category }}">{{ $category->category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" id="language" name="language" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                title="{{ trans('model.article.language') }}">
                            @foreach (config('common.language') as $key => $value)
                                <option value="{{ $key }}">
                                    <i class="fi fi-{{ $value[1] }}" aria-hidden="true"></i>
                                    <span style="padding: inherit;">{{ $value[0] }}</span>
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4 btn-group">
                        <button class="btn btn-primary" type="submit">{{ trans('common.search') }}</button>
                        <button class="btn btn-danger" type="button" onclick="resetSearchForm()">{{ trans('common.reset') }}</button>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                        <tr>
                            <th> #</th>
                            <th> {{ trans('model.common.type') }}</th>
                            <th> {{ trans('model.article.category') }}</th>
                            <th> {{ trans('validation.attributes.title') }}</th>
                            <th> {{ trans('model.article.language') }}</th>
                            <th> {{ trans('model.common.sort') }}</th>
                            <th> {{ trans('model.article.created_at') }}</th>
                            <th> {{ trans('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
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
                                    {{ Str::limit($article->category, 30) }}
                                </td>
                                <td class="text-left">
                                    @if ($article->logo)
                                        <img class="mr-5" src="{{ asset($article->logo) }}" alt="logo" style="height: 32px" loading="lazy" />
                                    @endif
                                    {{ Str::limit($article->title, 50) }}
                                </td>
                                <td>
                                    @if (isset(config('common.language')[$article->language]))
                                        <i class="fi fi-{{ config('common.language')[$article->language][1] }}" aria-hidden="true"></i>
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
                                                <a class="btn btn-outline-danger"
                                                   href="javascript:delArticle('{{ route('admin.article.destroy', $article->id) }}', '{{ $article->id }}')">
                                                    <i class="icon wb-close" aria-hidden="true"></i></a>
                                            @endcan
                                        </div>
                                    @endcanany
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.article.counts', ['num' => $articles->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{ $articles->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('javascript')
    @can('admin.article.destroy')
        <script>
            $(document).ready(function() {
                $('#type').selectpicker('val', @json(Request::query('type')))
                $('#category').selectpicker('val', @json(Request::query('category')))
                $('#language').selectpicker('val', @json(Request::query('language')))
            })

            // 删除文章
            function delArticle(url, id) {
                swal.fire({
                    title: '{{ trans('admin.confirm.delete.0', ['attribute' => trans('model.article.attribute')]) }}' +
                        id +
                        '{{ trans('admin.confirm.delete.1') }}',
                    icon: 'question',
                    showCancelButton: true,
                    cancelButtonText: '{{ trans('common.close') }}',
                    confirmButtonText: '{{ trans('common.confirm') }}',
                }).then((result) => {
                    if (result.value) {
                        $.ajax({
                            method: 'DELETE',
                            url: url,
                            data: {
                                _token: '{{ csrf_token() }}',
                            },
                            dataType: 'json',
                            success: function(ret) {
                                if (ret.status === 'success') {
                                    swal.fire({
                                        title: ret.message,
                                        icon: 'success',
                                        timer: 1000,
                                        showConfirmButton: false,
                                    }).then(() => window.location.reload())
                                } else {
                                    swal.fire({
                                        title: ret.message,
                                        icon: 'error',
                                    }).then(() => window.location.reload())
                                }
                            },
                        })
                    }
                })
            }
        </script>
    @endcan
@endpush
