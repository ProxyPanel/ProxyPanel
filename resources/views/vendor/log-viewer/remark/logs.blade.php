@extends('vendor.log-viewer.remark.layouts')

<?php
/** @var  Illuminate\Pagination\LengthAwarePaginator $rows */ ?>

@section('content')
    <div class="page-header">
        <h1>@lang('Logs')</h1>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                @foreach($headers as $key => $header)
                    <th scope="col" class="{{ $key == 'date' ? 'text-left' : 'text-center' }}">
                        @if ($key == 'date')
                            <strong>{{ $header }}</strong>
                        @else
                            <strong class="badge badge-level-{{ $key }}">
                                {{ log_styler()->icon($key) }} {{ $header }}
                            </strong>
                        @endif
                    </th>
                @endforeach
                <th scope="col" class="text-right">@lang('Actions')</th>
            </tr>
            </thead>
            <tbody>
            @forelse($rows as $date => $row)
                <tr>
                    @foreach($row as $key => $value)
                        <td class="{{ $key == 'date' ? 'text-left' : 'text-center' }}">
                            @if ($key == 'date')
                                <strong>{{ $value }}</strong>
                            @elseif ($value == 0)
                            @else
                                <a href="{{ route('log-viewer::logs.filter', [$date, $key]) }}">
                                    <span class="badge badge-level-{{ $key }}">{{ $value }}</span>
                                </a>
                            @endif
                        </td>
                    @endforeach
                    <td class="text-right">
                        <a href="{{ route('log-viewer::logs.show', [$date]) }}" class="btn btn-sm btn-info">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </a>
                        <a href="{{ route('log-viewer::logs.download', [$date]) }}" class="btn btn-sm btn-success">
                            <i class="fa-solid fa-download"></i>
                        </a>
                        <button class="btn btn-sm btn-danger" data-target="#deleteLogModal" data-toggle="modal" data-log-date="{{ $date }}" type="button">
                            <i class="fa-solid fa-trash"></i> @lang('Delete')
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center">
                        <span class="badge badge-secondary">@lang('The list of logs is empty!')</span>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $rows->render() }}
@endsection

@section('modals')
    {{-- DELETE MODAL --}}
    <div id="deleteLogModal" class="modal fade" aria-hidden="true" aria-labelledby="deleteLogModal"
         role="dialog" tabindex="-1">
        <div class="modal-dialog modal-simple modal-center">
            <form id="deleteLogForm" class="modal-content" action="{{ route('log-viewer::logs.delete') }}" method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('common.close') }}">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title">@lang('Delete log file')</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_method" value="DELETE">@csrf
                    <input type="hidden" name="date" value="">
                    <p></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary mr-auto" data-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn-danger" data-loading-text="@lang('Loading')&hellip;">@lang('Delete')</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
      $(function() {
        const deleteLogModal = $('div#deleteLogModal'),
            deleteLogForm = $('form#deleteLogForm'),
            submitBtn = deleteLogForm.find('button[type=submit]');

        $('button[data-target=\'#deleteLogModal\']').on('click', function(event) {
          event.preventDefault();
          const date = $(this).data('log-date'),
              message = "{{ __('Are you sure you want to delete this log file: :date ?') }}";

          deleteLogForm.find('input[name=date]').val(date);
          deleteLogModal.find('.modal-body p').html(message.replace(':date', date));

          deleteLogModal.modal('show');
        });

        deleteLogForm.on('submit', function(event) {
          event.preventDefault();
          submitBtn.button('loading');

          $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            dataType: 'json',
            data: $(this).serialize(),
            success: function(data) {
              submitBtn.button('reset');
              if (data.result === 'success') {
                deleteLogModal.modal('hide');
                location.reload();
              } else {
                alert('AJAX ERROR ! Check the console !');
                console.error(data);
              }
            },
            error: function(xhr, textStatus, errorThrown) {
              alert('AJAX ERROR ! Check the console !');
              console.error(errorThrown);
              submitBtn.button('reset');
            },
          });

          return false;
        });

        deleteLogModal.on('hidden.bs.modal', function() {
          deleteLogForm.find('input[name=date]').val('');
          deleteLogModal.find('.modal-body p').html('');
        });
      });
    </script>
@endsection
