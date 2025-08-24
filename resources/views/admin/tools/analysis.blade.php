@extends('admin.table_layouts')
@section('content')
    <div class="page-content container">
        <x-admin.table-panel :title="trans('admin.menu.tools.analysis') . ' <small>' . trans('admin.tools.analysis.sub_title') . '</small>'" :theads="[trans('admin.tools.analysis.req_url')]">
            @if (Session::has('analysisErrorMsg'))
                <x-slot:filters>
                    <x-alert type="danger" :message="Session::pull('analysisErrorMsg')" />
                </x-slot:filters>
            @endif
            <x-slot:tbody>
                @if (!empty($urlList))
                    @foreach ($urlList as $url)
                        <tr>
                            <td> {{ $url }} </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="1">{{ trans('admin.tools.analysis.not_enough') }}</td>
                    </tr>
                @endif
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>

@endsection
@push('javascript')
    <script>
        const TableDatatablesScroller = function() {
            const e = function() {
                const e = $("#analysis");
                e.dataTable({
                    language: {
                        aria: {
                            sortAscending: ": activate to sort column ascending",
                            sortDescending: ": activate to sort column descending"
                        },
                        emptyTable: "暂无数据",
                        info: "第 _START_ 到 _END_ 条，共计 _TOTAL_ 条",
                        infoEmpty: "未找到",
                        infoFiltered: "(filtered1 from _MAX_ total entries)",
                        lengthMenu: "_MENU_ entries",
                        search: "搜索:",
                        zeroRecords: "未找到"
                    },
                    buttons: [{
                            extend: "print",
                            className: "btn btn-outline-dark"
                        },
                        {
                            extend: "pdf",
                            className: "btn btn-outline-success"
                        },
                        {
                            extend: "csv",
                            className: "btn btn-outline-primary"
                        }
                    ],
                    scrollY: 300,
                    deferRender: !0,
                    scroller: !0,
                    stateSave: !0,
                    order: [
                        [0, "asc"]
                    ],
                    lengthMenu: [
                        [10, 15, 20, -1],
                        [10, 15, 20, "All"]
                    ],
                    pageLength: 20,
                    dom: "<'row' <'col-md-12'B>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>"
                });
            };
            return {
                init: function() {
                    jQuery().dataTable && (e());
                }
            };
        }();
        jQuery(document).ready(function() {
            TableDatatablesScroller.init();
        });
    </script>
@endpush
