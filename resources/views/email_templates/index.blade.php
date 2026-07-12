@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.email_templates') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.email_templates_table') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/email.png') }}" alt=""></span>
                            <h3 class="mb-0">{{ trans('lang.email_templates') }}</h3>
                            <span class="counter ml-3 total_count"></span>
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
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.email_templates_table') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.email_templates_table_text') }}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="emailTemplatesTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.type') }}</th>
                                            <th>{{ trans('lang.subject') }}</th>
                                            <th>{{ trans('lang.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="emailTemplatesTbody"></tbody>
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
    var database = kweekDb();
    var refData = database.collection('email_templates').orderBy('createdAt', 'desc');

    function templateTypeLabel(type) {
        var labels = {
            new_order_placed: "{{ trans('lang.new_order_placed') }}",
            new_vendor_signup: "{{ trans('lang.new_vendor_signup') }}",
            payout_request: "{{ trans('lang.payout_request') }}",
            payout_request_status: "{{ trans('lang.payout_request_status') }}",
            wallet_topup: "{{ trans('lang.wallet_topup') }}",
            new_ride_book: "{{ trans('lang.new_ride_book') }}",
            new_parcel_book: "{{ trans('lang.new_parcel_book') }}",
            new_car_book: "{{ trans('lang.new_car_book') }}",
            new_ondemand_book: "{{ trans('lang.new_ondemand_book') }}",
        };
        return labels[type] || type || '';
    }

    function buildHTML(snapshots) {
        var html = '';
        snapshots.docs.forEach(function (doc) {
            var data = doc.data();
            var id = doc.id;
            var route1 = '{{ route('email-templates.save', ':id') }}'.replace(':id', id);
            html += '<tr>';
            html += '<td>' + templateTypeLabel(data.type) + '</td>';
            html += '<td>' + (data.subject || '') + '</td>';
            html += '<td><span class="action-btn"><a href="' + route1 + '" data-toggle="tooltip" title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a></span></td>';
            html += '</tr>';
        });
        return html;
    }

    $(document).ready(function () {
        var appendList = document.getElementById('emailTemplatesTbody');
        jQuery("#data-table_processing").show();
        appendList.innerHTML = '';

        refData.get().then(function (snapshots) {
            var html = buildHTML(snapshots);
            $('.total_count').text(snapshots.docs.length);
            appendList.innerHTML = html;

            $('#emailTemplatesTable').DataTable({
                order: [[0, 'asc']],
                columnDefs: [{ orderable: false, targets: [2] }],
                language: {
                    zeroRecords: "{{ trans('lang.no_record_found') }}",
                    emptyTable: "{{ trans('lang.no_record_found') }}"
                },
                responsive: true
            });

            $('[data-toggle="tooltip"]').tooltip();
            jQuery("#data-table_processing").hide();
        });
    });
</script>
@endsection
