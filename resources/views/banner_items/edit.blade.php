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
                <li class="breadcrumb-item"><a href="{!! route('banners.index') !!}">{{trans('lang.menu_items')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.menu_items_edit')}}</li>
            </ol>
        </div>
    </div>
    
    <div class="container-fluid">
        <div class="error_topalert alert alert-danger" style="display:none;"></div>
        
        <div class="row vendor_payout_create">
            <div class="vendor_payout_create-inner">
                <div class="card border-0" style="position: relative; margin-top: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                    <div class="card-tab-badge">BANNER ITEMS</div>
                    
                    <div class="card-body pt-5">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold" style="color: #2b354e;">{{trans('lang.title')}} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control title" placeholder="Enter Banner Title">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold" style="color: #2b354e;">{{trans('lang.set_order')}} <span class="text-danger">*</span></label>
                                <input type="number" class="form-control set_order" min="0" placeholder="0">
                            </div>
                        </div>
                        
                        <div class="row mt-2">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold" style="color: #2b354e;">{{trans('lang.select_section')}} <span class="text-danger">*</span></label>
                                <select name="section_id" id="section_id" class="form-control">
                                    <option value="">{{trans('lang.select')}}</option>
                                </select>
                                <span class="form-text text-danger mt-1 d-block" style="font-size: 13px; font-weight: 500;">
                                    Note: Rental service sections won't shown in this list.
                                </span>
                            </div>
                            <div class="col-md-6 form-group" id="banner_position_div" style="display: none;">
                                <label class="font-weight-bold" style="color: #2b354e;">{{trans('lang.banner_position')}}</label>
                                <select name="position" id="position" class="form-control">
                                    <option value="top">{{trans('lang.top')}}</option>
                                    <option value="middle">{{trans('lang.middle')}}</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Dynamic Fields for Ecommerce/Delivery Services -->
                        <div id="dynamic_fields_container" style="display: none;">
                            <div class="row mt-3">
                                <div class="col-md-12 form-group">
                                    <div class="d-flex align-items-center mt-2">
                                        <div class="custom-control custom-radio mr-4">
                                            <input type="radio" id="redirect_store" name="redirect_type" value="store" class="custom-control-input redirect_type">
                                            <label class="custom-control-label font-weight-bold" for="redirect_store" style="cursor: pointer; color: #2b354e;">Store</label>
                                        </div>
                                        <div class="custom-control custom-radio mr-4">
                                            <input type="radio" id="redirect_product" name="redirect_type" value="product" class="custom-control-input redirect_type">
                                            <label class="custom-control-label font-weight-bold" for="redirect_product" style="cursor: pointer; color: #2b354e;">Product</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="redirect_external" name="redirect_type" value="external_link" class="custom-control-input redirect_type">
                                            <label class="custom-control-label font-weight-bold" for="redirect_external" style="cursor: pointer; color: #2b354e;">External Link</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-md-6 form-group" id="vendor_div" style="display: none;">
                                    <label class="font-weight-bold" style="color: #2b354e;">Store <span class="text-danger">*</span></label>
                                    <select name="storeId" id="storeId" class="form-control select2" style="width: 100%;">
                                        <option value="">Select Store</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group" id="product_div" style="display: none;">
                                    <label class="font-weight-bold" style="color: #2b354e;">Product <span class="text-danger">*</span></label>
                                    <select name="productId" id="productId" class="form-control select2" style="width: 100%;">
                                        <option value="">Select Product</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group" id="external_link_div" style="display: none;">
                                    <label class="font-weight-bold" style="color: #2b354e;">External Link <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="external_link" placeholder="Enter external link URL">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-12 form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_publish">
                                    <label class="custom-control-label font-weight-bold" for="is_publish" style="color: #2b354e; cursor: pointer;">{{trans('lang.is_publish')}}</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold" style="color: #2b354e;">{{trans('lang.app_banner')}} <span class="text-danger">*</span></label>
                                <input type="file" id="banner_img" accept="image/*" onChange="handleFileSelect(event)" class="form-control">
                                <div class="user_image mt-2"></div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold" style="color: #2b354e;">{{trans('lang.web_banner')}}</label>
                                <input type="file" id="web_banner_img" accept="image/*" onChange="handleWebBannerFileSelect(event)" class="form-control">
                                <div class="web_banner_image mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group col-12 text-center mt-4">
                    <button type="button" class="btn btn-save save-setting-btn"><i class="fa fa-save mr-1"></i> Save</button>
                    <a href="{!! route('banners.index') !!}" class="btn btn-back ml-2"><i class="fa fa-undo mr-1"></i> Back</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card-tab-badge {
        position: absolute;
        top: -18px;
        left: 20px;
        background-color: #000;
        color: #fff;
        padding: 8px 24px;
        font-size: 13px;
        font-weight: 700;
        border-radius: 6px;
        letter-spacing: 0.5px;
        z-index: 10;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    
    .preview-image-container {
        position: relative;
        display: inline-block;
        margin-top: 10px;
    }
    
    .preview-image-container img {
        width: 50px;
        height: 50px;
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
    
    .btn-save {
        background-color: #000 !important;
        border-color: #000 !important;
        color: #fff !important;
        font-weight: 600;
        padding: 10px 30px;
        border-radius: 6px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        transition: transform 0.2s, opacity 0.2s;
    }
    
    .btn-save:hover {
        transform: translateY(-1px);
        opacity: 0.9;
    }
    
    .btn-back {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        color: #fff !important;
        font-weight: 600;
        padding: 10px 30px;
        border-radius: 6px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        transition: transform 0.2s, opacity 0.2s;
    }
    
    .btn-back:hover {
        transform: translateY(-1px);
        opacity: 0.9;
    }
    
    .form-control:focus {
        border-color: #000 !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.05) !important;
    }

    /* Custom checkbox styling to match Image 1 (only checkmark, no blue box container when checked) */
    .custom-checkbox {
        padding-left: 0 !important;
    }
    .custom-checkbox .custom-control-input {
        opacity: 0;
        position: absolute;
        width: 100%;
        height: 100%;
        cursor: pointer;
        z-index: 2;
    }
    .custom-checkbox .custom-control-label {
        padding-left: 24px !important;
        position: relative;
        cursor: pointer;
        user-select: none;
    }
    .custom-checkbox .custom-control-label::before {
        display: none !important;
    }
    .custom-checkbox .custom-control-label::after {
        display: none !important;
    }
    /* Add a custom checkmark element when checked */
    .custom-checkbox .custom-control-label::before {
        content: '';
        position: absolute;
        left: 0;
        top: 3px;
        width: 15px;
        height: 15px;
        border: 1px solid #ced4da;
        border-radius: 3px;
        background-color: #fff;
        display: block !important;
    }
    .custom-checkbox .custom-control-input:checked ~ .custom-control-label::before {
        content: '✔' !important;
        border: none !important;
        background-color: transparent !important;
        color: #000 !important;
        font-size: 14px;
        line-height: 15px;
        text-align: center;
        font-weight: bold;
    }
</style>
@endsection

@section('scripts')
<script type="text/javascript">
    var photo = "";
    var fileName = "";
    var webPhoto = "";
    var webFileName = "";
    
    var id = "{{ $record->id }}";
    
    var adminDataUpsertUrl = '{{ url("admin-data/upsert") }}';
    var adminDataUploadUrl = '{{ url("admin-data/upload") }}';
    var adminDataDocumentUrl = '{{ url("admin-data/document/banner_items") }}/' + encodeURIComponent(id);
    var csrfToken = '{{ csrf_token() }}';
    
    $(document).ready(function() {
        // Load active sections first, then load the banner details
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
            
            // Load Banner details once sections dropdown is populated
            loadBannerDetails();
        })
        .catch(err => console.error('Failed to load sections:', err));
        
        // Handle Section Selection Change
        $('#section_id').change(function() {
            let sectionId = $(this).val();
            let serviceType = $(this).find(':selected').attr('data-service-type');
            
            if (serviceType === 'ecommerce-service' || serviceType === 'delivery-service') {
                $('#banner_position_div').show();
                $('#dynamic_fields_container').slideDown();
            } else {
                $('#banner_position_div').hide();
                $('#dynamic_fields_container').slideUp();
                $('#storeId').html('<option value="">Select Store</option>');
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
    
    function loadBannerDetails() {
        jQuery("#data-table_processing").show();
        
        fetch(adminDataDocumentUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(result => {
            jQuery("#data-table_processing").hide();
            
            let banner = result.data;
            if (!banner) return;
            
            $('.title').val(banner.title);
            $('.set_order').val(banner.set_order);
            $('#is_publish').prop('checked', filterBool(banner.is_publish));
            
            if (banner.sectionId) {
                $('#section_id').val(banner.sectionId).trigger('change');
            }
            
            if (banner.photo) {
                photo = banner.photo;
                $(".user_image").html(`
                    <div class="preview-image-container">
                        <img src="${photo}" alt="App Banner Preview">
                        <button type="button" class="remove-img-btn" onclick="removeAppBanner()">&times;</button>
                    </div>
                `);
            }
            
            if (banner.web_banner) {
                webPhoto = banner.web_banner;
                $(".web_banner_image").html(`
                    <div class="preview-image-container">
                        <img src="${webPhoto}" alt="Web Banner Preview">
                        <button type="button" class="remove-img-btn" onclick="removeWebBanner()">&times;</button>
                    </div>
                `);
            }
            
            // Set Redirection Details
            let serviceType = $('#section_id').find(':selected').attr('data-service-type');
            if (serviceType === 'ecommerce-service' || serviceType === 'delivery-service') {
                $('#banner_position_div').show();
                if (banner.position) {
                    $('#position').val(banner.position);
                }
                
                if (banner.redirect_type) {
                    $(`input[name="redirect_type"][value="${banner.redirect_type}"]`).prop('checked', true);
                    
                    if (banner.redirect_type === 'store') {
                        $('#vendor_div').show();
                        $('#product_div').hide();
                        $('#external_link_div').hide();
                        loadSectionVendors(banner.sectionId, banner.redirect_id);
                    } else if (banner.redirect_type === 'product') {
                        $('#vendor_div').hide();
                        $('#product_div').show();
                        $('#external_link_div').hide();
                        loadSectionProducts(banner.sectionId, banner.redirect_id);
                    } else if (banner.redirect_type === 'external_link') {
                        $('#vendor_div').hide();
                        $('#product_div').hide();
                        $('#external_link_div').show();
                        $('#external_link').val(banner.redirect_id);
                    }
                }
            }
        })
        .catch(err => {
            jQuery("#data-table_processing").hide();
            console.error(err);
        });
    }
    
    function filterBool(val) {
        return val === true || val === 'true' || val === 1 || val === '1';
    }
    
    function showError(msg) {
        $('.error_topalert').html('<p class="mb-0">' + msg + '</p>').show();
        window.scrollTo(0, 0);
    }
    
    function loadSectionVendors(sectionId, selectedId = '') {
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
            if (selectedId) {
                $('#storeId').val(selectedId);
            }
        })
        .catch(err => {
            console.error(err);
            $('#storeId').html('<option value="">Error loading vendors</option>');
        });
    }
    
    function loadSectionProducts(sectionId, selectedId = '') {
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
            if (selectedId) {
                $('#productId').val(selectedId);
            }
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
