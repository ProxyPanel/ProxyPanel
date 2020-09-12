@extends('auth.layouts')
@section('title',sysConfig('website_name'))
@section('content')
    <h4 class="caption-subject font-dark bold">{{trans('home.free_invite_codes_title')}}</h4>
    <div class="table-responsive">
        <table class="table table-hover text-center">
            @if(sysConfig('is_invite_register'))
                @if(sysConfig('is_free_code'))
                    <thead class="thead-default">
                    <tr>
                        <th> {{trans('home.invite_code_table_name')}} </th>
                        <th> {{trans('home.invite_code_table_date')}} </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($inviteList as $invite)
                        <tr>
                            <td><a href="/register?code={{$invite->code}}" target="_blank">{{$invite->code}}</a></td>
                            <td> {{$invite->dateline}} </td>
                        </tr>
                    @endforeach
                    </tbody>
                @endif
            @else
                <tbody>
                <tr>
                    <td colspan="2">{{trans('home.no_need_invite_codes')}}</td>
                </tr>
                </tbody>
            @endif
        </table>
    </div>
    @if(sysConfig('is_invite_register') && sysConfig('is_free_code'))
        <div class="mt-20">
            <a href="/login" class="btn btn-danger btn-lg float-left">{{trans('auth.back')}}</a>
            <nav class="Page navigation float-right">
                {{$inviteList->links()}}
            </nav>
        </div>
    @endif
@endsection
