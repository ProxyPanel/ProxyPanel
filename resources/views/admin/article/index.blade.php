@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">文章列表</h3>
                @can('admin.article.create')
                    <div class="panel-actions">
                        <a href="{{route('admin.article.create')}}" class="btn btn-primary"><i class="icon wb-plus"></i>添加文章</a>
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-xxl-1 col-lg-1 col-md-1 col-sm-4">
                        <input type="number" class="form-control" name="id" value="{{Request::query('id')}}" placeholder="ID"/>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" id="type" name="type">
                            <option value="" hidden>类 型</option>
                            <option value="1">文章</option>
                            <option value="2">公告</option>
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" id="category" name="category">
                            <option value="" hidden>分 类</option>
                            @foreach($categories as $category)
                                <option value="{{$category->category}}">{{$category->category}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" id="language" name="language">
                            <option value="" hidden>语 言</option>
                            @foreach (config('common.language') as $key => $value)
                                <option value="{{$key}}">
                                    <i class="fi fi-{{$value[1]}}"></i> <span style="padding: inherit;">{{$value[0]}}</span>
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4 btn-group">
                        <button type="submit" class="btn btn-primary">搜 索</button>
                        <a href="{{route('admin.article.index')}}" class="btn btn-danger">{{trans('common.reset')}}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 类 型</th>
                        <th> 分 类</th>
                        <th> 标 题</th>
                        <th> 语 言</th>
                        <th> 排 序</th>
                        <th> 发布日期</th>
                        <th> {{trans('common.action')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($articles as $article)
                        <tr>
                            <td> {{$article->id}} </td>
                            @if ($article->type === 1)
                                <td> 文章</td>
                            @elseif ($article->type === 2)
                                <td> 公告</td>
                            @else
                                <td> 未知</td>
                            @endif
                            <td class="text-left">
                                {{ Str::limit($article->category, 30) }}
                            </td>
                            <td class="text-left">
                                {{ Str::limit($article->title, 50) }}
                            </td>
                            <td>
                            {!! isset(config('common.language')[$article->language]) ? '<i class="fi fi-'.config('common.language')[$article->language][1].'"></i>
                             <span style="padding: inherit;">'.config('common.language')[$article->language][0].'</span>': __('user.unknown') !!}
                            <td> {{$article->sort}} </td>
                            <td> {{$article->created_at}} </td>
                            <td>
                                @canany(['admin.article.edit', 'admin.article.destroy'])
                                    <div class="btn-group">
                                        @can('admin.article.show')
                                            <a href="{{route('admin.article.show',$article)}}" class="btn btn-outline-success">
                                                <i class="icon wb-eye"></i></a>
                                        @endcan
                                        @can('admin.article.edit')
                                            <a href="{{route('admin.article.edit',['article'=>$article->id, 'page'=>Request::query('page')])}}" class="btn btn-outline-primary">
                                                <i class="icon wb-edit"></i></a>
                                        @endcan
                                        @can('admin.article.destroy')
                                            <a class="btn btn-outline-danger" href="javascript:delArticle('{{route('admin.article.destroy',$article->id)}}')">
                                                <i class="icon wb-close"></i></a>
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
                        共 <code>{{$articles->total()}}</code> 篇文章
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$articles->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    @can('admin.article.destroy')
        <script>
          $(document).ready(function() {
            $('#id').val('{{Request::query('id')}}');
            $('#type').val('{{Request::query('type')}}');
            $('#category').val('{{Request::query('category')}}');
            $('#language').val('{{Request::query('language')}}');
            $('select').on('change', function() { this.form.submit(); });
          });

          // 删除文章
          function delArticle(url) {
            swal.fire({
              title: '确定删除文章?',
              icon: 'question',
              showCancelButton: true,
              cancelButtonText: '{{trans('common.close')}}',
              confirmButtonText: '{{trans('common.confirm')}}',
            }).then((result) => {
              if (result.value) {
                $.ajax({
                  method: 'DELETE',
                  url: url,
                  data: {_token: '{{csrf_token()}}'},
                  dataType: 'json',
                  success: function(ret) {
                    if (ret.status === 'success') {
                      swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
                    } else {
                      swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
                    }
                  },
                });
              }
            });
          }
        </script>
    @endcan
@endsection
