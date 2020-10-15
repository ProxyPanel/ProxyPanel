@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">邮箱过滤列表
                    <small>（用于屏蔽注册邮箱后缀）</small>
                </h2>
                <div class="panel-actions">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#add_email_suffix"> 添加邮箱后缀
                    </button>
                </div>
            </div>
            <div class="panel-body">
                <table class="table text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 类型</th>
                        <th> 邮箱后缀</th>
                        <th> 操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($list as $vo)
                        <tr>
                            <td> {{$vo->id}} </td>
                            <td> {{$vo->type==1? '黑':'白'}} </td>
                            <td> {{$vo->words}} </td>
                            <td>
                                <button class="btn btn-danger" onclick="delSuffix('{{$vo->id}}','{{$vo->words}}')">
                                    <i class="icon wb-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$list->total()}}</code> 条记录
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$list->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="add_email_suffix" class="modal fade" tabindex="-1" data-focus-on="input:first" data-keyboard="false">
        <div class="modal-dialog modal-simple modal-center modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title"> 添加邮箱后缀 </h4>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="type">类型</label>
                        <div class="col-md-10 d-flex align-items-center">
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" value="1" checked/>
                                <label for="type">黑名单</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" value="2"/>
                                <label for="type">白名单</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="words">邮箱后缀</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="words" id="words" placeholder="请填入邮箱后缀"/>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-danger"> 关 闭</button>
                    <button data-dismiss="modal" class="btn btn-success" onclick="addEmailSuffix()"> 提 交</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        // 添加邮箱后缀
        function addEmailSuffix() {
            const words = $('#words').val();
            if (words.trim() === '') {
                swal.fire({title: '邮箱后缀不能为空', icon: 'warning', timer: 1000, showConfirmButton: false});
                $('#words').focus();
                return false;
            }

            $.post('{{route('admin.config.filter.store')}}', {
                _token: '{{csrf_token()}}',
                type: $('input:radio[name=\'type\']:checked').val(),
                words: words,
            }, function(ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
                }
                else {
                    swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
                }
            });
        }

        // 删除邮箱后缀
        function delSuffix(id, name) {
            swal.fire({
                title: '警告',
                text: '确定删除邮箱后缀 【' + name + '】 ？',
                icon: 'warning',
                showCancelButton: true,
                cancelButtonText: '取消',
                confirmButtonText: '确定',
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        method: 'DELETE',
                        url: '{{route('admin.config.filter.destroy', '')}}/' + id,
                        data: {_token: '{{csrf_token()}}'},
                        dataType: 'json',
                        success: function(ret) {
                            if (ret.status === 'success') {
                                swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
                            }
                            else {
                                swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
                            }
                        },
                    });
                }
            });
        }
    </script>
@endsection
