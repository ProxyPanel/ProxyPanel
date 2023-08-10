@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{!! trans('admin.setting.email.title') !!}
                </h2>
                @can('admin.config.filter.store')
                    <div class="panel-actions">
                        <button class="btn btn-primary" data-toggle="modal"
                                data-target="#add_email_suffix"> {{ trans('admin.action.add_item', ['attribute' => trans('admin.setting.email.tail')]) }}
                        </button>
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <table class="table text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> {{ trans('admin.setting.email.rule') }}</th>
                        <th> {{ trans('admin.setting.email.tail') }}</th>
                        <th> {{ trans('common.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($filters as $filter)
                        <tr>
                            <td> {{$filter->id}} </td>
                            <td> {{$filter->type === 1? trans('admin.setting.email.black') : trans('admin.setting.email.white')}} </td>
                            <td> {{$filter->words}} </td>
                            <td>
                                @can('admin.config.filter.destroy')
                                    <button class="btn btn-danger" onclick="delSuffix('{{$filter->id}}','{{$filter->words}}')">
                                        <i class="icon wb-trash"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.logs.counts', ['num' => $filters->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$filters->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('admin.config.filter.store')
        <div id="add_email_suffix" class="modal fade" tabindex="-1" data-focus-on="input:first" data-keyboard="false">
            <div class="modal-dialog modal-simple modal-center modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('common.close') }}">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title"> {{ trans('admin.action.add_item', ['attribute' => trans('admin.setting.email.tail')]) }} </h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-form-label col-md-2" for="type">{{ trans('admin.setting.email.rule') }}</label>
                            <div class="col-md-10 d-flex align-items-center">
                                <div class="radio-custom radio-primary radio-inline">
                                    <input type="radio" name="type" value="1" checked/>
                                    <label for="type">{{ trans('admin.setting.email.black') }}</label>
                                </div>
                                <div class="radio-custom radio-primary radio-inline">
                                    <input type="radio" name="type" value="2"/>
                                    <label for="type">{{ trans('admin.setting.email.white') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-md-2" for="words">{{ trans('admin.setting.email.tail') }}</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="words" id="words" placeholder="{{ trans('admin.setting.email.tail_placeholder') }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button data-dismiss="modal" class="btn btn-danger mr-auto">{{ trans('common.close') }}</button>
                        <button data-dismiss="modal" class="btn btn-success" onclick="addEmailSuffix()">{{ trans('common.submit') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script>
        @can('admin.config.filter.store')
        // 添加邮箱后缀
        function addEmailSuffix() {
          const words = $('#words').val();
          if (words.trim() === '') {
            swal.fire({
              title: '{{ trans('validation.required', ['attribute' => trans('admin.setting.email.tail')]) }}',
              icon: 'warning',
              timer: 1000,
              showConfirmButton: false,
            });
            $('#words').focus();
            return false;
          }

          $.post('{{route('admin.config.filter.store')}}', {
            _token: '{{csrf_token()}}',
            type: $('input:radio[name=\'type\']:checked').val(),
            words: words,
          }, function(ret) {
            if (ret.status === 'success') {
              swal.fire({
                title: ret.message,
                icon: 'success',
                timer: 1000,
                showConfirmButton: false,
              }).then(() => window.location.reload());
            } else {
              swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
            }
          });
        }
        @endcan

        @can('admin.config.filter.destroy')
        // 删除邮箱后缀
        function delSuffix(id, name) {
          swal.fire({
            title: '{{ trans('common.warning') }}',
            text: '{{ trans('admin.confirm.delete.0', ['attribute' => trans('admin.setting.email.tail')]) }}' + name +
                '{{ trans('admin.confirm.delete.1') }}',
            icon: 'warning',
            showCancelButton: true,
            cancelButtonText: '{{ trans('common.cancel') }}',
            confirmButtonText: '{{ trans('common.confirm') }}',
          }).then((result) => {
            if (result.value) {
              $.ajax({
                method: 'DELETE',
                url: '{{route('admin.config.filter.destroy', '')}}/' + id,
                data: {_token: '{{csrf_token()}}'},
                dataType: 'json',
                success: function(ret) {
                  if (ret.status === 'success') {
                    swal.fire({
                      title: ret.message,
                      icon: 'success',
                      timer: 1000,
                      showConfirmButton: false,
                    }).then(() => window.location.reload());
                  } else {
                    swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
                  }
                },
              });
            }
          });
        }
        @endcan
    </script>
@endsection
