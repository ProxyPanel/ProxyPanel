@extends('user.layouts')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <!-- BEGIN PORTLET -->
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{$info->title}}<sub class="ml-30">{{$info->created_at}}</sub></h3>
                        <div class="panel-body pt-0 pb-60">
                            {!! $info->content !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END CONTENT BODY -->
@endsection