@extends('user.layouts')
@section('css')
    <link href="/assets/global/fonts/themify/themify.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-lg-8 order-lg-1 order-2">
                <div class="panel panel-bordered">
                    <div class="panel-heading p-20">
                        <h1 class="panel-title cyan-600">
                            <i class="icon wb-user-circle"></i>{{ trans('user.menu.tickets') }}
                        </h1>
                        <div class="panel-actions">
                            <button class="btn btn-primary btn-animate btn-animate-side" data-toggle="modal" data-target="#add_ticket_modal">
                                <span>
                                    <i class="icon wb-plus" aria-hidden="true"></i> {{ trans('user.ticket.new') }}
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-hover text-center">
                                <thead class="thead-default">
                                    <tr>
                                        <th data-cell-style="cellStyle"> #</th>
                                        <th> {{ ucfirst(trans('validation.attributes.title')) }} </th>
                                        <th> {{ trans('common.status.attribute') }} </th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tickets as $ticket)
                                        <tr>
                                            <td>{{ $ticket->id }}</td>
                                            <td>{{ $ticket->title }}</td>
                                            <td>{!! $ticket->status_label !!}</td>
                                            <td>
                                                <a class="btn btn-animate btn-animate-vertical btn-outline-info" href="{{ route('ticket.edit', $ticket) }}">
                                                    <span>
                                                        @if ($ticket->status === 2)
                                                            <i class="icon wb-eye" aria-hidden="true" style="left: 40%"> </i>{{ trans('common.view') }}
                                                        @else
                                                            <i class="icon wb-check" aria-hidden="true" style="left: 40%"> </i>{{ trans('common.open') }}
                                                        @endif
                                                    </span>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-md-12">
                                <nav class="Page navigation float-right">
                                    {{ $tickets->links() }}
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 order-lg-2 order-1">
                <div class="panel panel-bordered">
                    <div class="panel-heading p-20">
                        <h3 class="panel-title cyan-600">
                            <i class="icon ti-headphone-alt"></i>{{ trans('user.ticket.service_hours') }}
                        </h3>
                    </div>
                    <div class="panel-body pt-0">
                        <ul class="list-group list-group-dividered list-group-full vertical-align-middle">
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-lg-5 col-4">
                                        <button class="btn btn-pure ti-time blue-700"></button>
                                        {{ trans('user.ticket.online_hour') }}
                                    </div>
                                    <div class="col-lg-7 col-8 text-right">
                                        {{ trans('common.days.work') }} 23:00 - {{ trans('common.days.next') }} 11:00
                                        <br>
                                        {{ trans('common.days.weekend') }} 21:00 - {{ trans('common.days.next') }} 12:00
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-2">
                                        <button class="btn btn-pure ti-info-alt red-700"></button>
                                    </div>
                                    <div class="col-10">
                                        {!! trans('user.ticket.service_tips') !!}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="add_ticket_modal" data-focus-on="input:first" data-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-simple modal-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button class="close" data-dismiss="modal" type="button" aria-label="{{ trans('common.close') }}">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title"> {{ trans('user.ticket.new') }} </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xl-12 form-group">
                            <input class="form-control" id="title" name="title" type="text" placeholder="{{ trans('user.ticket.title_placeholder') }}">
                        </div>
                        <div class="col-xl-12 form-group">
                            <textarea class="form-control" id="content" name="content" rows="5" placeholder="{{ trans('user.ticket.content_placeholder') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger mr-auto" data-dismiss="modal" type="button"> {{ trans('common.cancel') }} </button>
                    <button class="btn btn-success" data-dismiss="modal" type="button" onclick="createTicket()"> {{ trans('common.confirm') }} </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script>
        // 发起工单
        function createTicket() {
            const title = $('#title').val();
            const content = $('#content').val();

            if (title.trim() === '') {
                showMessage({
                    title: '{{ ucfirst(trans('validation.required', ['attribute' => trans('validation.attributes.title')])) }}!',
                    icon: 'warning'
                });
                return false;
            }

            if (content.trim() === '') {
                showMessage({
                    title: '{{ ucfirst(trans('validation.required', ['attribute' => trans('validation.attributes.content')])) }}!',
                    icon: 'warning'
                });
                return false;
            }

            showConfirm({
                title: '{{ trans('user.ticket.submit_tips') }}',
                onConfirm: function() {
                    ajaxPost('{{ route('ticket.store') }}', {
                        title: title,
                        content: content
                    });
                }
            });
        }

        $(document).ready(function() {
            $('#create_ticket_form').on('submit', function(e) {
                e.preventDefault();
                createTicket();
            });
        });
    </script>
@endsection
