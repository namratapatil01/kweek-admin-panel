@extends('layouts.app')
@section('content')
<div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor restaurantTitle">{{trans('lang.driver_document_details')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('drivers') !!}">{{trans('lang.driver_plural')}}</a></li>
                    <li class="breadcrumb-item active">{{trans('lang.driver_document_details')}}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                                <li class="nav-item">
                                    <a class="nav-link active vendor-name"
                                       href="{!! url()->current() !!}">{{trans('lang.driver_document_details')}}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10 doc-body"></div>
                            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog"
                                 aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document" style="max-width: 50%;">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close"
                                                    data-dismiss="modal"
                                                    aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <embed id="docImage"
                                                       src=""
                                                       frameBorder="0"
                                                       scrolling="auto"
                                                       height="100%"
                                                       width="100%"
                                                       style="height: 540px;"
                                                ></embed>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">{{trans('lang.close')}}</button>
                                            </div>
                                        </div>
                                    </div>
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
<script>
    var id = "<?php echo $id;?>";
    var fcmToken = "";

    $(document).ready(function () {
        jQuery("#data-table_processing").show();

        $('#exampleModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var img = button.data('image');
            var modal = $(this);
            modal.find('#docImage').attr('src', img);
        });

        var getDocsUrl = "{{ route('drivers.get-documents', ':id') }}".replace(':id', id);
        $.get(getDocsUrl, function (response) {
            var driver     = response.driver;
            var documents  = response.documents;
            var verified   = response.verified; // array of {documentId, status, frontImage, backImage}

            // Store fcmToken for notifications
            if (driver.fcmToken && driver.fcmToken !== '') {
                fcmToken = driver.fcmToken;
            }

            $(".vendor-name").text(driver.firstName + ' ' + driver.lastName + "'s {{trans('lang.driver_document_details')}}");

            var html = '';
            html += '<table id="taxTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">';
            html += '<thead><tr><th>Name</th><th>Status</th><th>Action</th></tr></thead>';
            html += '<tbody>';

            if (documents && documents.length > 0) {
                documents.forEach(function (doc) {
                    // Find verified entry for this document
                    var docRef = null;
                    if (verified && verified.length > 0) {
                        for (var i = 0; i < verified.length; i++) {
                            if (verified[i].documentId == doc.id) {
                                docRef = verified[i];
                                break;
                            }
                        }
                    }

                    var trhtml = '<tr>';

                    // Title + image preview links
                    if (docRef && docRef.backImage !== undefined && docRef.frontImage !== undefined) {
                        var hasFront = docRef.frontImage && docRef.frontImage !== '';
                        var hasBack  = docRef.backImage  && docRef.backImage  !== '';
                        if (hasFront && hasBack && doc.backSide && doc.frontSide) {
                            trhtml += '<td>' + doc.title + '&nbsp;&nbsp;'
                                + '<a href="#" class="badge badge-info" data-toggle="modal" data-target="#exampleModal" data-image="' + docRef.frontImage + '" data-id="front">{{trans('lang.view_front_image')}}</a>&nbsp;'
                                + '<a href="#" class="badge badge-info" data-toggle="modal" data-target="#exampleModal" data-image="' + docRef.backImage  + '" data-id="back">{{trans('lang.view_back_image')}}</a>'
                                + '</td>';
                        } else if (hasBack && doc.backSide) {
                            trhtml += '<td>' + doc.title + '&nbsp;<a href="#" class="badge badge-info" data-toggle="modal" data-target="#exampleModal" data-id="back" data-image="' + docRef.backImage + '">{{trans('lang.view_back_image')}}</a></td>';
                        } else if (hasFront && doc.frontSide) {
                            trhtml += '<td>' + doc.title + '&nbsp;<a href="#" class="badge badge-info" data-toggle="modal" data-target="#exampleModal" data-id="front" data-image="' + docRef.frontImage + '">{{trans('lang.view_front_image')}}</a></td>';
                        } else {
                            trhtml += '<td>' + doc.title + '</td>';
                        }
                    } else {
                        trhtml += '<td>' + doc.title + '</td>';
                    }

                    // Status badge
                    var status = 'pending';
                    if (docRef) {
                        if (docRef.status === 'approved')  status = 'approved';
                        else if (docRef.status === 'rejected') status = 'rejected';
                        else if (docRef.status === 'uploaded') status = 'uploaded';
                    }
                    var display_status = '';
                    if (status === 'approved')  display_status = '<span class="badge badge-success py-2 px-3">approved</span>';
                    else if (status === 'rejected') display_status = '<span class="badge badge-danger py-2 px-3">rejected</span>';
                    else if (status === 'uploaded') display_status = '<span class="badge badge-primary py-2 px-3">uploaded</span>';
                    else                            display_status = '<span class="badge badge-warning py-2 px-3">pending</span>';
                    trhtml += '<td>' + display_status + '</td>';

                    // Action buttons
                    trhtml += '<td class="action-btn">';
                    trhtml += '<a href="/drivers/document/upload/' + id.trim() + '/' + doc.id + '" data-id="' + doc.id + '"><i class="mdi mdi-lead-pencil" title="Edit"></i></a>&nbsp;';

                    var hasUploaded = docRef && ((docRef.frontImage && docRef.frontImage !== '') || (docRef.backImage && docRef.backImage !== ''));
                    if (hasUploaded || status === 'approved' || status === 'rejected' || status === 'uploaded') {
                        if (status === 'approved') {
                            trhtml += '&nbsp;<a href="javascript:void(0);" class="btn btn-sm btn-danger direct-click-btn verify-doc" id="disapprove-doc" data-title="' + doc.title + '" data-id="' + doc.id + '">{{trans('lang.reject')}}</a>';
                        } else if (status === 'rejected') {
                            trhtml += '&nbsp;<a href="javascript:void(0);" class="btn btn-sm btn-success direct-click-btn verify-doc" id="approve-doc" data-title="' + doc.title + '" data-id="' + doc.id + '">{{trans('lang.approve')}}</a>';
                        } else {
                            trhtml += '&nbsp;<a href="javascript:void(0);" class="btn btn-sm btn-success direct-click-btn verify-doc" id="approve-doc" data-title="' + doc.title + '" data-id="' + doc.id + '">{{trans('lang.approve')}}</a>'
                                   + '&nbsp;<a href="javascript:void(0);" class="btn btn-sm btn-danger verify-doc" id="disapprove-doc" data-title="' + doc.title + '" data-id="' + doc.id + '">{{trans('lang.reject')}}</a>';
                        }
                    }
                    trhtml += '</td></tr>';
                    html += trhtml;
                });
            }

            html += '</tbody></table>';
            $(".doc-body").append(html);

            $('#taxTable').DataTable({
                order: [[0, 'asc']],
                columnDefs: [{ orderable: false, targets: [1, 2] }],
            });

            jQuery("#data-table_processing").hide();
        }).fail(function (xhr) {
            jQuery("#data-table_processing").hide();
            console.error('Error loading documents:', xhr.responseJSON);
        });
    });

    $(document.body).on('click', '.redirecttopage', function () {
        window.location.href = $(this).attr('data-url');
    });

    $(document).on('click', '.verify-doc', function () {
        jQuery("#data-table_processing").show();
        var status   = $(this).attr('id') === "approve-doc" ? "approved" : "rejected";
        var docId    = $(this).attr('data-id');
        var docTitle = $(this).attr('data-title');

        $.ajax({
            url: "{{ route('drivers.verify-document') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id:     id,
                docId:  docId,
                status: status
            },
            success: function (response) {
                var notifTitle   = status === 'approved' ? 'Approved your document'  : 'Rejected your document';
                var notifMessage = status === 'approved'
                    ? 'Admin has Approved your ' + docTitle
                    : 'Admin has Rejected your ' + docTitle + '. Please submit again.';

                if (fcmToken && fcmToken !== '') {
                    $.ajax({
                        url: "{{ route('advertisement.sendnotification') }}",
                        type: "POST",
                        data: {
                            _token:  "{{ csrf_token() }}",
                            fcm:     fcmToken,
                            title:   notifTitle,
                            message: notifMessage
                        },
                        complete: function () {
                            jQuery("#data-table_processing").hide();
                            window.location.reload();
                        }
                    });
                } else {
                    jQuery("#data-table_processing").hide();
                    window.location.reload();
                }
            },
            error: function (xhr) {
                jQuery("#data-table_processing").hide();
                var err = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Verification failed';
                $(".error_top").show().html("<p>" + err + "</p>");
            }
        });
    });
</script>
@endsection
