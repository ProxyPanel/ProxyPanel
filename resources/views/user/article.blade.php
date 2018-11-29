@extends('user.layouts')
@section('css')
@endsection
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN PORTLET -->
                <div class="portlet light bordered">
                    <div class="portlet-title tabbable-line">
                        <div class="caption">
                            <i class="icon-directions font-green hide"></i>
                            <span class="caption-subject bold font-dark uppercase"> {{$info->title}} </span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="tab-content">
                            {!! $info->content !!}
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
