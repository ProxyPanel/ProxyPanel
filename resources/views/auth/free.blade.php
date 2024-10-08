@extends('auth.layouts')
@section('title', sysConfig('website_name'))
@section('content')
    <h4 class="caption-subject font-dark bold">{{ '[' . trans('common.free') . '] ' . trans('user.invite.attribute') }}</h4>
    <div class="table-responsive">
        <table class="table table-hover text-center">
            @if (sysConfig('is_invite_register'))
                @if (sysConfig('is_free_code'))
                    <thead class="thead-default">
                        <tr>
                            <th> {{ trans('user.invite.attribute') }} </th>
                            <th> {{ trans('common.available_date') }} </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inviteList as $invite)
                            <tr>
                                <td>
                                    <a href="{{ route('register', ['code' => $invite->code]) }}" target="_blank">{{ $invite->code }}</a>
                                </td>
                                <td> {{ $invite->dateline }} </td>
                            </tr>
                        @endforeach
                    </tbody>
                @endif
            @else
                {{ trans('auth.invite.not_required') }}
            @endif
        </table>
    </div>
    @if (sysConfig('is_invite_register') && sysConfig('is_free_code'))
        <div class="mt-20">
            <a class="btn btn-danger btn-lg float-left" href="{{ route('login') }}">{{ trans('common.back') }}</a>
            <nav class="Page navigation float-right">
                {{ $inviteList->links() }}
            </nav>
        </div>
    @endif
@endsection
