@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{{ trans('admin.aff.commission_title') }}</h2>
                <div class="panel-actions">
                    @if($referral->status === -1)
                        <span class="badge badge-lg badge-danger"> {{ trans('common.status.rejected') }} </span>
                    @elseif($referral->status === 2)
                        <span class="badge badge-lg badge-success"> {{ trans('common.status.paid') }} </span>
                    @endif
                    <a href="{{route('admin.aff.index')}}" class="btn btn-danger">{{ trans('common.back') }}</a>
                </div>
            </div>
            <div class="panel-body">
                <div class="example">
                    <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                        <thead class="thead-default">
                        <tr>
                            <th colspan="6">
                                {{ trans('model.referral.id') }}: {{$referral->id}}
                                |{{ trans('model.referral.user') }}: {{$referral->user->username}}
                                |{{ trans('model.referral.amount') }}: {{$referral->amount_tag}}
                                | {{ trans('model.referral.created_at') }}: {{$referral->created_at}}
                            </th>
                        </tr>
                        <tr>
                            <th> #</th>
                            <th> {{ trans('model.aff.invitee') }}</th>
                            <th> {{ trans('model.order.id') }}</th>
                            <th> {{ trans('model.aff.amount') }}</th>
                            <th> {{ trans('model.aff.commission') }}</th>
                            <th> {{ trans('model.aff.created_at') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($commissions as $commission)

                            <tr>
                                <td> {{$commission->id}} </td>
                                <td> {{$commission->invitee->username ?? '【'.trans('common.deleted_item', ['attribute' => trans('common.account')]).'】'}} </td>
                                <td>
                                    @can('admin.order')
                                        <a href="{{route('admin.order', ['id' => $commission->order->id])}}" target="_blank">
                                            {{$commission->order->goods->name}}
                                        </a>
                                    @else
                                        {{$commission->order->goods->name}}
                                    @endcan
                                </td>
                                <td> {{$commission->amount_tag}} </td>
                                <td> {{$commission->commission_tag}} </td>
                                <td> {{$commission->created_at}} </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.aff.commission_counts', ['num' => $commissions->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$commissions->links()}}
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
@endsection
