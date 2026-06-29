<script type="text/javascript">
$(function () {
    var table = $('#moduleTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route($routePrefix . ".datatable") }}',
            type: 'GET',
            data: function (d) {
                d.sectionId = getCookie('section_id') || '';
            }
        },
        order: [[2, 'desc']],
        pageLength: 10,
        language: {
            zeroRecords: "{{ trans('lang.no_record_found') }}",
            emptyTable: "{{ trans('lang.no_record_found') }}",
            processing: '<i class="fa fa-spinner fa-spin"></i> Loading...'
        }
    });

    $('#select-all').on('click', function () {
        $('.row-select').prop('checked', $(this).is(':checked'));
    });

    $(document).on('click', '.delete-row', function () {
        var id = $(this).data('id');
        if (!confirm('{{ trans("lang.delete_alert") }}')) return;

        $.ajax({
            url: '{{ url($routePrefix) }}/' + id,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function () { table.ajax.reload(); },
            error: function (xhr) { alert(xhr.responseJSON?.error || 'Delete failed'); }
        });
    });
});
</script>
