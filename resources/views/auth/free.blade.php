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
                        @forelse ($inviteList as $invite)
                            <tr>
                                <td>
                                    <a href="{{ route('register', ['code' => $invite->code]) }}" target="_blank">{{ $invite->code }}</a>
                                </td>
                                <td> {{ $invite->dateline }} </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="2">
                                    <span class="badge badge-secondary">{{ trans('common.none') }}</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                @endif
            @else
                {{ trans('auth.invite.not_required') }}
            @endif
        </table>
    </div>
@endsection
@section('footer')
    @if (sysConfig('is_invite_register') && sysConfig('is_free_code'))
        <div class="panel-footer d-flex justify-content-between">
            <div>
                <a class="btn btn-danger" href="{{ route('login') }}">{{ trans('common.back') }}</a>
            </div>
            {{ $inviteList->links() }}
        </div>
    @endif
@endsection
