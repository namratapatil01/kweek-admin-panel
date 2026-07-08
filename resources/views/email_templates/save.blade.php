@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">
                @if($id == '')
                    {{ trans('lang.create_email_templates') }}
                @else
                    {{ trans('lang.edit_email_templates') }}
                @endif
            </h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ url('email-templates') }}">{{ trans('lang.email_templates') }}</a></li>
                <li class="breadcrumb-item active">
                    @if($id == '')
                        {{ trans('lang.create_email_templates') }}
                    @else
                        {{ trans('lang.edit_email_templates') }}
                    @endif
                </li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card border">
            <div class="card-body">
                <div class="error_top" style="display:none"></div>
                <div class="success_top" style="display:none"></div>

                <div class="row vendor_payout_create">
                    <div class="vendor_payout_create-inner">
                        <fieldset>
                            <legend>{{ trans('lang.email_template') }}</legend>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.type') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control" id="type" @if($id != '') readonly @endif>
                                    <input type="hidden" id="type_key">
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.subject') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control" id="subject">
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.message') }}</label>
                                <div class="col-12">
                                    <textarea class="form-control col-7" name="message" id="message"></textarea>
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <div class="form-check width-100">
                                    <input type="checkbox" id="is_send_to_admin">
                                    <label class="col-3 control-label" for="is_send_to_admin">{{ trans('lang.is_send_to_admin') }}</label>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>

                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-primary edit-setting-btn">
                        <i class="fa fa-save"></i> {{ trans('lang.save') }}
                    </button>
                    <a href="{{ url('email-templates') }}" class="btn btn-default">
                        <i class="fa fa-undo"></i>{{ trans('lang.cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var requestId = @json($id);
    var database = kweekDb();
    var createdAt = kweekDb.FieldValue.serverTimestamp();
    var id = requestId === '' ? database.collection('tmp').doc().id : requestId;

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

    $('#message').summernote({
        height: 400,
        width: '100%',
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['forecolor', ['forecolor']],
            ['backcolor', ['backcolor']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['view', ['fullscreen', 'codeview', 'help']],
        ]
    });

    $(document).ready(function () {
        if (requestId !== '') {
            jQuery("#data-table_processing").show();
            database.collection('email_templates').where('id', '==', id).get().then(function (snapshots) {
                if (snapshots.docs.length) {
                    var data = snapshots.docs[0].data();
                    $("#subject").val((data.subject || '').trim());
                    $('#message').summernote('code', data.message || '');
                    $("#is_send_to_admin").prop('checked', !!data.isSendToAdmin);
                    $('#type_key').val(data.type || '');
                    $('#type').val(templateTypeLabel(data.type));
                }
                jQuery("#data-table_processing").hide();
            });
        }
    });

    $(".edit-setting-btn").click(function () {
        $(".success_top").hide();
        $(".error_top").hide();

        var subject = $("#subject").val().trim();
        var message = $('#message').summernote('code');
        var type = requestId !== '' ? $('#type_key').val() : $('#type').val().trim();
        var isSendToAdmin = $("#is_send_to_admin").is(":checked");

        if (subject === "") {
            $(".error_top").show().html("<p>{{ trans('lang.please_enter_subject') }}</p>");
            window.scrollTo(0, 0);
            return false;
        }

        if (!message || message === '<p><br></p>') {
            $(".error_top").show().html("<p>{{ trans('lang.please_enter_message') }}</p>");
            window.scrollTo(0, 0);
            return false;
        }

        jQuery("#data-table_processing").show();

        if (requestId === '') {
            database.collection('email_templates').doc(id).set({
                id: id,
                subject: subject,
                message: message,
                type: type,
                isSendToAdmin: isSendToAdmin,
                createdAt: createdAt,
            }).then(function () {
                jQuery("#data-table_processing").hide();
                window.location.href = '{{ route('email-templates.index') }}';
            }).catch(function (error) {
                jQuery("#data-table_processing").hide();
                $(".error_top").show().html("<p>" + error + "</p>");
            });
            return;
        }

        database.collection('email_templates').doc(id).update({
            subject: subject,
            message: message,
            isSendToAdmin: isSendToAdmin,
        }).then(function () {
            jQuery("#data-table_processing").hide();
            window.location.href = '{{ route('email-templates.index') }}';
        }).catch(function (error) {
            jQuery("#data-table_processing").hide();
            $(".error_top").show().html("<p>" + error + "</p>");
        });
    });
</script>
@endsection
