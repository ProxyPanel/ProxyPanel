@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{{ trans('admin.aff.rebate_title') }}</h2>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-4 col-sm-6">
                        <input type="text" class="form-control" name="invitee_username" value="{{Request::query('invitee_username')}}"
                               placeholder="{{ trans('model.aff.invitee') }}"/>
                    </div>
                    <div class="form-group col-lg-4 col-sm-6">
                        <input type="text" class="form-control" name="inviter_username" value="{{Request::query('inviter_username')}}"
                               placeholder="{{ trans('model.user.inviter') }}"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                            <option value="" hidden>{{ trans('common.status.attribute') }}</option>
                            <option value="0">{{ trans('common.status.unwithdrawn') }}</option>
                            <option value="1">{{ trans('common.status.applying') }}</option>
                            <option value="2">{{ trans('common.status.withdrawn') }}</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button type="submit" class="btn btn-primary">{{ trans('common.search') }}</button>
                        <a href="{{route('admin.aff.rebate')}}" class="btn btn-danger">{{ trans('common.reset') }}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> {{ trans('model.aff.invitee') }}</th>
                        <th> {{ trans('model.user.inviter') }}</th>
                        <th> {{ trans('model.order.id') }}</th>
                        <th> {{ trans('model.aff.amount') }}</th>
                        <th> {{ trans('model.aff.commission') }}</th>
                        <th> {{ trans('model.aff.created_at') }}</th>
                        <th> {{ trans('model.aff.updated_at') }}</th>
                        <th> {{ trans('common.status.attribute') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($referralLogs as $referralLog)
                        <tr>
                            <td> {{$referralLog->id}} </td>
                            <td>
                                @if(empty($referralLog->invitee))
                                    【{{trans('common.deleted_item', ['attribute' => trans('common.account')])}}】
                                @else
                                    <a href="{{route('admin.aff.rebate',['invitee_username' => $referralLog->invitee->username])}}"> {{$referralLog->invitee->username}} </a>
                                @endif
                            </td>
                            <td>
                                @if(empty($referralLog->inviter))
                                    【{{trans('common.deleted_item', ['attribute' => trans('common.account')])}}】
                                @else
                                    <a href="{{route('admin.aff.rebate',['inviter_username' => $referralLog->inviter->username])}}"> {{$referralLog->inviter->username}} </a>
                                @endif
                            </td>
                            <td> {{$referralLog->order_id}} </td>
                            <td> {{$referralLog->amount}} </td>
                            <td> {{$referralLog->commission}} </td>
                            <td> {{$referralLog->created_at}} </td>
                            <td> {{$referralLog->updated_at}} </td>
                            <td>
                                @if ($referralLog->status === 1)
                                    <span class="badge badge-danger">{{ trans('common.status.applying') }}</span>
                                @elseif($referralLog->status === 2)
                                    <span class="badge badge-default">{{ trans('common.status.withdrawn') }}</span>
                                @else
                                    <span class="badge badge-info">{{ trans('common.status.unwithdrawn') }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.aff.counts', ['num' => $referralLogs->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$referralLogs->links()}}
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
    <script>
      $(document).ready(function() {
        $('#status').val({{Request::query('status')}});
      });
    </script>
@endsection
