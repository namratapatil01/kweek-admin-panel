@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.upload_document')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('drivers') !!}">{{trans('lang.driver_plural')}}</a>
                </li>
                <li class="breadcrumb-item active">{{trans('lang.upload_document')}}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card  pb-4">
            <div class="card-body">
                <div class="error_top"></div>
                <div class="row vendor_payout_create">
                    <div class="vendor_payout_create-inner doc-body">
                    </div>
                </div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-primary save-form-btn"><i class="fa fa-save"></i> {{
    trans('lang.save')}}
                    </button>
                    <a href="{{url('drivers/document-list/' . $driverId)}}" class="btn btn-default"><i
                            class="fa fa-undo"></i>{{
    trans('lang.cancel')}}</a>
                </div>
            </div>
        </div>
    </div>
    @endsection
    @section('scripts')
    <script>
        var docId = "{{$id}}";
        var id = "{{$driverId}}";
        var storageRef = kweekStorage().ref('images');
        var storage = kweekStorage();
        var back_photo = '';
        var front_photo = '';
        var backFileName = '';
        var frontFileName = '';
        var backFileOld = '';
        var frontFileOld = '';
        var placeholderImage = '';

        // Load placeholder from MySQL settings
        $.get("{{ route('drivers.meta') }}", function (meta) {
            placeholderImage = meta.placeholderImage || '';
        });

        $(document).ready(function () {
            jQuery("#data-table_processing").show();

            var loadUrl = "{{ route('drivers.get-document-upload', [':driverId', ':id']) }}"
                            .replace(':driverId', id)
                            .replace(':id', docId.trim());

            $.get(loadUrl, function (response) {
                var doc       = response.document;
                var verifyDoc = response.verified_doc;
                var html = '';

                if (doc && doc.enable) {
                    html += '<fieldset><legend>' + doc.title + '</legend>';
                    html += doc.backSide ? '<div class="form-group row width-50">' : '<div class="form-group row width-100">';

                    if (doc.frontSide) {
                        front_photo  = verifyDoc && verifyDoc.frontImage ? verifyDoc.frontImage : '';
                        frontFileOld = front_photo;
                        html += '<input type="hidden" name="frontSide" id="frontSide" value="true">';
                        html += '<label class="col-3 control-label">{{trans("lang.front_image")}}<span class="required-field"></span></label>'
                              + '<div class="col-7"><input type="file" onChange="handleFrontFileSelect(event)" class="form-control image">'
                              + '<div class="placeholder_img_thumb front_image"><span class="image-item">'
                              + '<span class="remove-btn" id="front_image"><i class="fa fa-remove"></i></span>'
                              + '<img class="rounded" style="width:200px;height:auto" src="' + (front_photo || placeholderImage) + '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">'  
                              + '</span></div><div id="uploding_image"></div></div></div>';
                    }

                    if (doc.backSide) {
                        back_photo  = verifyDoc && verifyDoc.backImage ? verifyDoc.backImage : '';
                        backFileOld = back_photo;
                        html += '<input type="hidden" name="backSide" id="backSide" value="true">';
                        html += '<div class="form-group row width-50"><label class="col-3 control-label">{{trans("lang.back_image")}}<span class="required-field"></span></label>'
                              + '<div class="col-7"><input type="file" onChange="handleBackFileSelect(event)" class="form-control image">'
                              + '<div class="placeholder_img_thumb back_image"><span class="image-item">'
                              + '<span class="remove-btn" id="back_image"><i class="fa fa-remove"></i></span>'
                              + '<img class="rounded" style="width:200px;height:auto" src="' + (back_photo || placeholderImage) + '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">'  
                              + '</span></div><div id="uploding_image"></div></div></div>';
                    }

                    html += '<input type="hidden" name="docId" id="docId" value="' + doc.id + '">';
                    html += '</fieldset>';
                }
                $(".doc-body").html(html);
                jQuery("#data-table_processing").hide();
            }).fail(function (xhr) {
                jQuery("#data-table_processing").hide();
                console.error('Error loading document upload:', xhr.responseJSON);
            });
        });

        async function storeImageData() {
            var newPhoto = [];
            newPhoto['front_img'] = front_photo;
            newPhoto['back_img'] = back_photo;
            return newPhoto;
        }

        function handleFrontFileSelect(evt) {
            var f = evt.target.files[0];
            var validExtensions = ['jpg', 'jpeg', 'png'];
            var fileExtension = f.name.split('.').pop().toLowerCase();
            if (validExtensions.indexOf(fileExtension) === -1) {
                alert("{{trans('lang.invalid_file_extension')}}");
                return;
            }
            var reader = new FileReader();
            reader.onload = (function (theFile) {
                return function (e) {
                    var filePayload = e.target.result;
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                    var timestamp = Number(new Date());
                    var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                    front_photo = filePayload;
                    frontFileName = filename;
                    $(".front_image").empty();
                    $(".front_image").append('<span class="image-item"><span class="remove-btn" id="front_image"><i class="fa fa-remove"></i></span><img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" class="rounded" style="width:200px; height:auto" src="' + filePayload + '" alt="image"></span>');
                };
            })(f);
            reader.readAsDataURL(f);
        }
        function handleBackFileSelect(evt) {
            var f = evt.target.files[0];
            var validExtensions = ['jpg', 'jpeg', 'png'];
            var fileExtension = f.name.split('.').pop().toLowerCase();
            if (validExtensions.indexOf(fileExtension) === -1) {
                alert("{{trans('lang.invalid_file_extension')}}");
                return;
            }
            var reader = new FileReader();
            reader.onload = (function (theFile) {
                return function (e) {
                    var filePayload = e.target.result;
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                    var timestamp = Number(new Date());
                    var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                    back_photo = filePayload;
                    backFileName = filename;
                    $(".back_image").empty();
                    $(".back_image").append('<span class="image-item"><span class="remove-btn" id="back_image"><i class="fa fa-remove"></i></span><img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" class="rounded" style="width:200px; height:auto" src="' + filePayload + '" alt="image"></span>');
                };
            })(f);
            reader.readAsDataURL(f);
        }
        $(document).on('click', '.save-form-btn', function () {
            var docId    = $("#docId").val();
            var backSide = $("#backSide").val();
            var frontSide= $("#frontSide").val();
            if (backSide && back_photo == "") {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.document_back_side_help')}}</p>");
                window.scrollTo(0, 0);
            } else if (frontSide && front_photo == "") {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.document_front_side_help')}}</p>");
                window.scrollTo(0, 0);
            } else {
                jQuery("#data-table_processing").show();
                storeImageData().then(IMG => {
                    $.ajax({
                        url: "{{ route('drivers.save-document-upload') }}",
                        type: "POST",
                        data: {
                            _token:     "{{ csrf_token() }}",
                            driverId:   id,
                            docId:      docId,
                            frontImage: IMG.front_img || '',
                            backImage:  IMG.back_img  || '',
                            status:     'uploaded'
                        },
                        success: function () {
                            jQuery("#data-table_processing").hide();
                            window.location.href = "/drivers/document-list/" + id;
                        },
                        error: function (xhr) {
                            jQuery("#data-table_processing").hide();
                            var err = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Upload failed';
                            $(".error_top").show().html("<p>" + err + "</p>");
                        }
                    });
                }).catch(err => {
                    jQuery("#data-table_processing").hide();
                    $(".error_top").show().html("<p>" + err + "</p>");
                    window.scrollTo(0, 0);
                });
            }
        });
        $(document).on('click', '.remove-btn', function () {
            var currentId = $(this).attr('id');
            if (currentId == "back_image")  { $(".back_image").empty();  back_photo = '';  backFileName  = ''; }
            if (currentId == "front_image") { $(".front_image").empty(); front_photo = ''; frontFileName = ''; }
        });
    </script>
    @endsection