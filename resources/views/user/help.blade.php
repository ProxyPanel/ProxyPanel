@extends('user.layouts')
@section('css')
    <link href="/assets/pages/css/search.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top: 0;">
        <div class="search-page search-content-1">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light">
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject font-dark bold">{{trans('home.help')}}</span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="search-container">
                                @if($articleList->isEmpty())
                                    <div style="text-align: center;"><h3>{{trans('home.invoice_table_none')}}</h3></div>
                                @else
                                    <ul>
                                        @foreach($articleList as $key => $article)
                                            <li class="search-item clearfix">
                                                <a href="javascript:;">
                                                    @if($article->logo)
                                                        <img src="{{url($article->logo)}}" style="max-width: 100px; max-height: 75px;">
                                                    @else
                                                        <img src="{{asset('assets/images/noimage.png')}}">
                                                    @endif
                                                </a>
                                                <div class="search-content">
                                                    <h2 class="search-title">
                                                        <a href="{{url('article?id=') . $article->id}}">{{str_limit($article->title, 300)}}</a>
                                                    </h2>
                                                    <p class="search-desc" style="font-size: 16px;"> {{$article->summary}} </p>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                {{ $articleList->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
@endsection
