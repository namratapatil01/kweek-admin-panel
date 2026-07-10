@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.item_attribute_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.attribute_table') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/attribute.png') }}" alt=""></span>
                            <h3 class="mb-0">{{ trans('lang.item_attribute_plural') }}</h3>
                            <span class="counter ml-3 total_count">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.attribute_table') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.attribute_table_text') }}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a class="btn-primary btn rounded-full" href="{{ route('attributes.create') }}">
                                        <i class="mdi mdi-plus mr-2"></i>{{ trans('lang.attribute_create') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="attributesTable"
                                    class="display nowrap table table-hover table-striped table-bordered"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.attribute_name') }}</th>
                                            <th>{{ trans('lang.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script type="text/javascript">
    var user_permissions = [];
    try {
        user_permissions = JSON.parse('<?php echo @session('user_permissions') ?: '[]'; ?>' || '[]');
    } catch (e) {
        user_permissions = [];
    }

    var checkDeletePermission = false;

    if ($.inArray('item.attributes.delete', user_permissions) >= 0 || {{ (int) (auth()->user()->role_id ?? 0) === 1 ? 'true' : 'false' }}) {
        checkDeletePermission = true;
    }
    var database = kweekDb();
    var pagesize = 10;
    var ref = database.collection('vendor_attributes');
    var append_list = '';

    $(document).ready(function() {
        append_list = document.getElementById('append_list1');
        append_list.innerHTML = '';

        ref.get().then(async function(snapshots) {
            $('.total_count').text(snapshots.docs.length || 0);
            var html = await buildHTML(snapshots);
            $(function () {
                $('[data-toggle="tooltip"]').tooltip();
            });
            jQuery("#data-table_processing").hide();
            if (html != '') {
                append_list.innerHTML = html;
            }
            $('#attributesTable').DataTable({
                order: [[0, 'asc']],
                columnDefs: [{
                    orderable: false,
                    targets: [1]
                }],
                language: {
                    zeroRecords: "{{trans('lang.no_record_found')}}",
                    emptyTable: "{{trans('lang.no_record_found')}}"
                },
                error: function () {
                    jQuery('#data-table_processing').hide();
                }
            },
            order: [[0, 'asc']],
            columnDefs: [{
                orderable: false,
                targets: [1]
            }],
            language: {
                zeroRecords: "{{ trans('lang.no_record_found') }}",
                emptyTable: "{{ trans('lang.no_record_found') }}",
                processing: ''
            },
            drawCallback: function () {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });

        $(document).on('click', '.attribute-delete', function () {
            if (!checkDeletePermission) {
                return;
            }

            var id = $(this).data('id');
            if (!confirm('{{ trans('lang.delete_alert') }}')) {
                return;
            }

            jQuery('#data-table_processing').show();
            $.ajax({
                url: '{{ url('attributes') }}/' + id,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function () {
                    table.ajax.reload();
                    jQuery('#data-table_processing').hide();
                },
                error: function (xhr) {
                    jQuery('#data-table_processing').hide();
                    alert(xhr.responseJSON?.error || '{{ trans('lang.error') }}');
                }
            });
        }).catch(function (err) {
            console.error('Failed to load item attributes', err);
            $('.total_count').text(0);
            jQuery("#data-table_processing").hide();
        });
    });

    async function buildHTML(snapshots) {
        var html = '';
        (snapshots.docs || []).forEach(function (listval) {
            var val = listval.data() || {};
            val.id = listval.id;
            html += getListData(val);
        });
        return html;
    }

    function getListData(val) {
        var id = val.id;
        var route1 = '{{ url("attributes/edit") }}/' + encodeURIComponent(id);
        var title = val.title || val.name || id;
        var html = '<tr>';
        html += '<td>' + title + '</td>';
        html += '<td><span class="action-btn"><a href="' + route1 + '" data-toggle="tooltip" title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
        if (checkDeletePermission) {
            html += '<a id="' + id + '" name="attribute-delete" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
        }
        html += '</span></td></tr>';
        return html;
    }

    $(document).on("click", "a[name='attribute-delete']", function(e) {
        var id = this.id;
        database.collection('vendor_attributes').doc(id).delete().then(function(result) {
            window.location.href = '{{ route("attributes")}}';
        });
    });
</script>
@endsection
