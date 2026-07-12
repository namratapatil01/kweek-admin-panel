@extends('layouts.app')

@section('content')
<div class="page-wrapper">

    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.menu_items')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>

                <li class="breadcrumb-item"><a href="{!! route('banners.index') !!}">{{trans('lang.menu_items')}}</a>
                </li>

                <li class="breadcrumb-item active">{{trans('lang.menu_items_create')}}</li>

            </ol>

        </div>
    </div>

    <div class="card-body">

        <div class="error_topalert alert alert-danger" style="display:none;"></div>

        <div class="row vendor_payout_create">

            <div class="vendor_payout_create-inner">

                <fieldset>

                    <legend>
                        {{trans('lang.menu_items')}}
                    </legend>

                    <div class="form-group row width-50">

                        <label class="col-3 control-label">{{trans('lang.title')}}</label>

                        <div class="col-7">

                            <input type="text" class="form-control title">

                        </div>

                    </div>
                    <div class="form-group row width-50">

                        <label class="col-3 control-label">{{trans('lang.set_order')}}</label>

                        <div class="col-7">

                            <input type="number" class="form-control set_order" min="0">

                        </div>

                    </div>


                    <div class="form-group row width-50">
                        <label class="col-3 control-label ">{{trans('lang.select_section')}}</label>
                        <div class="col-7">
                            <select name="section_id" id="section_id" class="form-control">
                               
                                <option value="">{{trans('lang.select')}}</option>
                            </select>
                            <p style="color: red;font-size: 13px;"> Note: Rental service sections won't shown in this list.
                            </p>
                        </div>
                    </div>

                    <div class="form-group row width-100">

                        <div class="form-check width-100">

                            <input type="checkbox" id="is_publish" checked>

                            <label class="col-3 control-label" for="is_publish">{{trans('lang.is_publish')}}</label>

                        </div>

                    </div>

                    <!-- Dynamic Fields for Ecommerce/Delivery Services -->
                    <div id="dynamic_fields_container" style="display: none;" class="width-100">
                        <div class="form-group row width-50" id="banner_position">
                            <label class="col-3 control-label ">{{trans('lang.banner_position')}}</label>
                            <div class="col-7">
                                <select name="position" id="position" class="form-control">
                                    <option value="top">{{trans('lang.top')}}</option>
                                    <option value="middle">{{trans('lang.middle')}}</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row width-100 radio-form-row" id="redirect_type_div">
                            <div class="radio-form col-md-2">
                                <input type="radio" class="redirect_type" value="store" name="redirect_type" id="redirect_store">
                                <label class="custom-control-label">{{trans('lang.vendor')}}</label>
                            </div>

                            <div class="radio-form col-md-2">
                                <input type="radio" class="redirect_type" value="product" name="redirect_type" id="redirect_product">
                                <label class="custom-control-label">{{trans('lang.product')}}</label>
                            </div>

                            <div class="radio-form col-md-4">
                                <input type="radio" class="redirect_type" value="external_link" name="redirect_type" id="redirect_external" checked>
                                <label class="custom-control-label">{{trans('lang.external_link')}}</label>
                            </div>
                        </div>

                        <div class="form-group row width-50" id="vendor_div" style="display: none;">
                            <label class="col-3 control-label ">{{trans('lang.vendor')}}</label>
                            <div class="col-7">
                                <select name="storeId" id="storeId" class="form-control">
                                    <option value="">Select Vendor</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row width-50" id="product_div" style="display: none;">
                            <label class="col-3 control-label ">{{trans('lang.product')}}</label>
                            <div class="col-7">
                                <select name="productId" id="productId" class="form-control">
                                    <option value="">Select Product</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row width-100" id="external_link_div">
                            <label class="col-3 control-label">{{trans('lang.external_link')}}</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="external_link">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row width-50">

                        <label class="col-3 control-label">{{trans('lang.app_banner')}}</label>
                        <div class="col-7">
                            <input type="file" id="banner_img" onChange="handleFileSelect(event)">
                            <div id="uploding_image"></div>
                        </div>
                        <div class="placeholder_img_thumb user_image"></div>
                    </div>
                    <div class="form-group row width-50">
                    
                        <label class="col-3 control-label">{{trans('lang.web_banner')}}</label>
                        <div class="col-7">
                            <input type="file" id="web_banner_img" onChange="handleWebBannerFileSelect(event)">
                            <div id="uploding_image"></div>
                        </div>
                        <div class="placeholder_img_thumb web_banner_image"></div>
                    </div>

                </fieldset>

            </div>
        </div>

    </div>

    <div class="form-group col-12 text-center btm-btn">

        <button type="button" class="btn btn-primary save-setting-btn"><i class="fa fa-save"></i>
            {{trans('lang.save')}}
        </button>

        <a href="{!! route('banners.index') !!}" class="btn btn-default"><i
                class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>

    </div>

</div>

<style>
    .preview-image-container {
        position: relative;
        display: inline-block;
        margin-top: 10px;
    }
    
    .preview-image-container img {
        width: 120px;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .preview-image-container .remove-img-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #ef4444;
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 12px;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        transition: background-color 0.2s;
    }
    
    .preview-image-container .remove-img-btn:hover {
        background-color: #dc2626;
    }
</style>
@endsection

@section('scripts')
<script type="text/javascript">
    var photo = "";
    var fileName = "";
    var webPhoto = "";
    var webFileName = "";
    
    var adminDataUpsertUrl = '{{ url("admin-data/upsert") }}';
    var adminDataUploadUrl = '{{ url("admin-data/upload") }}';
    var csrfToken = '{{ csrf_token() }}';
    
    $(document).ready(function() {
        // Load active sections
        fetch('{{ route("api.sections") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(result => {
            let sections = result.data || [];
            
            // Define Service Group Mappings
            let groups = {
                'delivery-service': { label: 'Multivendor Delivery Service', options: [] },
                'ecommerce-service': { label: 'Ecommerce Service', options: [] },
                'parcel_delivery': { label: 'Parcel Delivery Service', options: [] },
                'cab-service': { label: 'Cab Service', options: [] },
                'ondemand-service': { label: 'On Demand Service', options: [] }
            };
            
            sections.forEach(sec => {
                if (groups[sec.serviceTypeFlag]) {
                    groups[sec.serviceTypeFlag].options.push(sec);
                }
            });
            
            Object.keys(groups).forEach(key => {
                let group = groups[key];
                if (group.options.length > 0) {
                    let $optgroup = $('<optgroup></optgroup>').attr('label', group.label);
                    group.options.forEach(sec => {
                        $optgroup.append(
                            $('<option></option>')
                                .attr('value', sec.id)
                                .attr('data-service-type', sec.serviceTypeFlag)
                                .text(sec.name)
                        );
                    });
                    $('#section_id').append($optgroup);
                }
            });
        })
        .catch(err => console.error('Failed to load sections:', err));
        
        // Handle Section Selection Change
        $('#section_id').change(function() {
            let sectionId = $(this).val();
            let serviceType = $(this).find(':selected').attr('data-service-type');
            
            if (serviceType === 'ecommerce-service' || serviceType === 'delivery-service') {
                $('#dynamic_fields_container').slideDown();
                // Select external link radio by default when shown
                $('#redirect_external').prop('checked', true).trigger('change');
            } else {
                $('#dynamic_fields_container').slideUp();
                $('#storeId').html('<option value="">Select Vendor</option>');
                $('#productId').html('<option value="">Select Product</option>');
                $('#external_link').val('');
            }
        });
        
        // Handle Redirection Type Radio Change
        $('input[name="redirect_type"]').change(function() {
            let type = $(this).val();
            let sectionId = $('#section_id').val();
            
            if (type === 'store') {
                $('#vendor_div').show();
                $('#product_div').hide();
                $('#external_link_div').hide();
                loadSectionVendors(sectionId);
            } else if (type === 'product') {
                $('#vendor_div').hide();
                $('#product_div').show();
                $('#external_link_div').hide();
                loadSectionProducts(sectionId);
            } else {
                $('#vendor_div').hide();
                $('#product_div').hide();
                $('#external_link_div').show();
            }
        });
        
        // Save Setting Action
        $('.save-setting-btn').click(async function() {
            let title = $('.title').val().trim();
            let setOrder = parseInt($('.set_order').val());
            let sectionId = $('#section_id').val();
            let isPublish = $('#is_publish').is(':checked');
            
            let serviceType = $('#section_id').find(':selected').attr('data-service-type');
            let position = 'top';
            let redirectType = '';
            let redirectId = '';
            
            $('.error_topalert').hide().html('');
            
            if (!title) {
                showError("{{trans('lang.title_banner_error')}}");
                return;
            }
            if (isNaN(setOrder) || setOrder < 0) {
                showError("{{trans('lang.set_order_error')}}");
                return;
            }
            if (!sectionId) {
                showError("{{trans('lang.set_section_error')}}");
                return;
            }
            
            if (serviceType === 'ecommerce-service' || serviceType === 'delivery-service') {
                position = $('#position').val();
                redirectType = $('input[name="redirect_type"]:checked').val();
                
                if (redirectType === 'store') {
                    redirectId = $('#storeId').val();
                    if (!redirectId) {
                        showError("{{trans('lang.set_store_error')}}");
                        return;
                    }
                } else if (redirectType === 'product') {
                    redirectId = $('#productId').val();
                    if (!redirectId) {
                        showError("{{trans('lang.set_product_error')}}");
                        return;
                    }
                } else if (redirectType === 'external_link') {
                    redirectId = $('#external_link').val().trim();
                    if (!redirectId) {
                        showError("{{trans('lang.set_external_error')}}");
                        return;
                    }
                }
            }
            
            if (!photo) {
                showError("{{trans('lang.please_choose_banner')}}");
                return;
            }
            
            jQuery("#data-table_processing").show();
            
            try {
                const appBannerUrl = await uploadFile(photo, fileName, 'banners');
                const webBannerUrl = webPhoto ? await uploadFile(webPhoto, webFileName, 'banners') : '';
                
                let id = 'banner_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                
                let payload = {
                    collection: 'banner_items',
                    id: id,
                    data: {
                        id: id,
                        title: title,
                        photo: appBannerUrl,
                        web_banner: webBannerUrl,
                        set_order: setOrder,
                        is_publish: isPublish,
                        sectionId: sectionId,
                        position: position,
                        redirect_type: redirectType,
                        redirect_id: redirectId
                    },
                    merge: false
                };
                
                const response = await fetch(adminDataUpsertUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                
                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw result.message || 'Failed to save banner.';
                }
                
                window.location.href = '{!! route("banners.index") !!}';
            } catch (err) {
                jQuery("#data-table_processing").hide();
                showError(err);
            }
        });
    });
    
    function showError(msg) {
        $('.error_topalert').html('<p class="mb-0">' + msg + '</p>').show();
        window.scrollTo(0, 0);
    }
    
    function loadSectionVendors(sectionId) {
        $('#storeId').html('<option value="">Loading Vendors...</option>');
        
        fetch('{{ url("admin-data/query") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                collection: 'vendors',
                filters: [{ field: 'section_id', op: '==', value: sectionId }]
            })
        })
        .then(response => response.json())
        .then(result => {
            let list = result.data || [];
            let html = '<option value="">Select Vendor</option>';
            list.forEach(item => {
                html += `<option value="${item.id}">${item.title}</option>`;
            });
            $('#storeId').html(html);
        })
        .catch(err => {
            console.error(err);
            $('#storeId').html('<option value="">Error loading vendors</option>');
        });
    }
    
    function loadSectionProducts(sectionId) {
        $('#productId').html('<option value="">Loading Products...</option>');
        
        fetch('{{ url("admin-data/query") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                collection: 'vendor_products',
                filters: [{ field: 'section_id', op: '==', value: sectionId }]
            })
        })
        .then(response => response.json())
        .then(result => {
            let list = result.data || [];
            let html = '<option value="">Select Product</option>';
            list.forEach(item => {
                html += `<option value="${item.id}">${item.name}</option>`;
            });
            $('#productId').html(html);
        })
        .catch(err => {
            console.error(err);
            $('#productId').html('<option value="">Error loading products</option>');
        });
    }
    
    function handleFileSelect(evt) {
        var f = evt.target.files[0];
        if (!f) return;
        
        var reader = new FileReader();
        reader.onload = function(e) {
            photo = e.target.result;
            fileName = f.name;
            
            $(".user_image").html(`
                <div class="preview-image-container">
                    <img src="${photo}" alt="App Banner Preview">
                    <button type="button" class="remove-img-btn" onclick="removeAppBanner()">&times;</button>
                </div>
            `);
        };
        reader.readAsDataURL(f);
    }
    
    function removeAppBanner() {
        photo = "";
        fileName = "";
        $('#banner_img').val('');
        $(".user_image").empty();
    }
    
    function handleWebBannerFileSelect(evt) {
        var f = evt.target.files[0];
        if (!f) return;
        
        var reader = new FileReader();
        reader.onload = function(e) {
            webPhoto = e.target.result;
            webFileName = f.name;
            
            $(".web_banner_image").html(`
                <div class="preview-image-container">
                    <img src="${webPhoto}" alt="Web Banner Preview">
                    <button type="button" class="remove-img-btn" onclick="removeWebBanner()">&times;</button>
                </div>
            `);
        };
        reader.readAsDataURL(f);
    }
    
    function removeWebBanner() {
        webPhoto = "";
        webFileName = "";
        $('#web_banner_img').val('');
        $(".web_banner_image").empty();
    }
    
    async function uploadFile(base64Data, originalName, directory) {
        if (!base64Data || base64Data.startsWith('http://') || base64Data.startsWith('https://')) {
            return base64Data;
        }
        
        const mimeMatch = base64Data.match(/^data:(image\/[a-zA-Z0-9+.-]+);base64,/);
        const contentType = mimeMatch ? mimeMatch[1] : 'image/jpeg';
        
        const responseBlob = await fetch(base64Data);
        const blob = await responseBlob.blob();
        
        const formData = new FormData();
        formData.append('file', blob, originalName || 'banner-image.jpg');
        formData.append('directory', directory);
        
        const response = await fetch(adminDataUploadUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw result.message || 'Image upload failed.';
        }
        
        return result.url;
    }
</script>
@endsection
