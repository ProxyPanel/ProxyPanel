@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    @stack('css')
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script>
        function resetSearchForm() {
            window.location.href = window.location.href.split('?')[0];
        }

        $('form').on('submit', function() {
            $(this).find('input, select').each(function() {
                if (!$(this).val()) {
                    $(this).remove();
                }
            });
        });

        $('select').on('change', function() {
            $(this).closest('form').trigger('submit');
        });
    </script>
    @stack('javascript')
@endsection
