@extends('admin.layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <!-- BEGIN PORTLET -->
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{$article->title}}<sub class="ml-30">{{$article->created_at}}</sub></h3>
                        <div class="panel-body pt-0 pb-60">
                            {!! $article->content !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
