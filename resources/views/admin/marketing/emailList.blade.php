@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('admin.marketing.email.title') }}</h3>
                <div class="panel-actions">
                    <button class="btn btn-primary" onclick="send()">
                        <i class="icon wb-envelope"></i>{{ trans('admin.marketing.email.group_send') }}</button>
                </div>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-3 col-sm-6">
                        <select class="form-control" id="status" name="status" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                title="{{ trans('common.status.attribute') }}">
                            <option value="0">{{ trans('common.to_be_send') }}</option>
                            <option value="-1">{{ trans('common.failed') }}</option>
                            <option value="1">{{ trans('common.success') }}</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-3 col-sm-6 btn-group">
                        <button class="btn btn-primary" type="submit">{{ trans('common.search') }}</button>
                        <button class="btn btn-danger" type="button" onclick="resetSearchForm()">{{ trans('common.reset') }}</button>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                        <tr>
                            <th> #</th>
                            <th> {{ trans('validation.attributes.title') }}</th>
                            <th> {{ trans('validation.attributes.content') }}</th>
                            <th> {{ trans('admin.marketing.send_status') }}</th>
                            <th> {{ trans('admin.marketing.send_time') }}</th>
                            <th> {{ trans('admin.marketing.error_message') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($emails as $email)
                            <tr>
                                <td> {{ $email->id }} </td>
                                <td> {{ $email->title }} </td>
                                <td> {{ $email->content }} </td>
                                <td> {{ $email->status_label }} </td>
                                <td> {{ $email->created_at }} </td>
                                <td> {{ $email->error }} </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.marketing.email.counts', ['num' => $emails->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{ $emails->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('javascript')
    <script>
        $(document).ready(function() {
            $('#status').selectpicker('val', @json(Request::query('status')));
        });

        // 发送邮件
        function send() {
            swal.fire(@json(trans('common.sorry')), '{{ trans('common.developing') }}', 'info');
        }
    </script>
@endpush
