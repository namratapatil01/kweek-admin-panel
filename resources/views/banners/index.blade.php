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
                <li class="breadcrumb-item active">{{trans('lang.menu_items')}}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="d-flex align-items-center mb-4">
            <span class="mr-2" style="font-size: 22px; display: inline-flex; align-items: center;">
                <i class="mdi mdi-checkbox-blank-outline" style="color: #4b5563;"></i>
            </span>
            <span class="section-header-title">{{trans('lang.menu_items')}}</span>
            <span class="count-badge ml-2 total_count">0</span>
        </div>

        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center bg-white border-0 pt-4 pb-2">
                            <div>
                                <h4 class="mb-1" style="font-weight: 700; color: #2b354e; font-size: 18px;">{{trans('lang.menu_items')}}</h4>
                                <p class="mb-0 text-muted" style="font-size: 13px;">{{trans('lang.menu_items_table_text')}}</p>
                            </div>
                            <div> 
                                <a class="btn btn-create-banner" href="{!! route('banners.create') !!}">
                                    <i class="mdi mdi-plus mr-1" style="font-size: 16px; font-weight: 700;"></i>{{trans('lang.menu_items_create')}}
                                </a>
                            </div>                
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table id="example24" class="table table-hover table-striped" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                        <?php if (in_array('banners.delete', json_decode(@session('user_permissions')))) { ?>
                                            <th class="delete-all" style="width: 90px; vertical-align: middle;">
                                                <div class="d-flex align-items-center">
                                                    <input type="checkbox" id="is_active" class="mr-2">
                                                    <a id="deleteAll" class="do_not_delete d-inline-flex align-items-center text-danger" href="javascript:void(0)" style="font-weight: 600; font-size: 13px; text-decoration: none;">
                                                        <i class="fa fa-trash mr-1"></i> {{trans('lang.all')}}
                                                    </a>
                                                </div>
                                            </th>
                                        <?php }?>
                                            <th>{{trans('lang.banner_info')}}</th>
                                            <th>{{trans('lang.banner_position')}}</th>
                                            <th>{{trans('lang.item_publish')}}</th>
                                            <th>{{trans('lang.actions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_vendors">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Section Title & Badge */
    .section-header-title {
        font-size: 20px;
        font-weight: 700;
        color: #2b354e;
    }
    .count-badge {
        background-color: #ffe9e3;
        color: #ff5e3a;
        font-weight: 700;
        font-size: 13px;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Card styling */
    .card {
        border-radius: 12px !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03) !important;
        border: 1px solid #eef2f6 !important;
        background-color: #fff;
    }

    /* Table styling */
    #example24 {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    #example24 thead th {
        background-color: #fff !important;
        border-bottom: 2px solid #f4f6f9 !important;
        color: #4b5563 !important;
        font-weight: 600 !important;
        font-size: 14px;
        padding: 12px 16px !important;
        border-top: none !important;
    }
    #example24 tbody tr {
        background-color: #fff !important;
        transition: background-color 0.2s ease;
    }
    #example24 tbody td {
        padding: 16px !important;
        vertical-align: middle !important;
        border-top: 1px solid #f4f6f9 !important;
        border-bottom: 1px solid #f4f6f9 !important;
        font-size: 14px;
        color: #333;
    }
    #example24 tbody tr:hover {
        background-color: #fafbfc !important;
    }

    /* Banner Info cell styling */
    .banner-info-container {
        display: flex;
        align-items: center;
    }
    .banner-img {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        object-fit: cover;
        margin-right: 15px;
        border: 1px solid #eef2f6;
    }
    .banner-name-link {
        font-weight: 600;
        color: #000 !important;
        text-decoration: underline !important;
        font-size: 14px;
    }
    .banner-name-link:hover {
        color: #ff5e3a !important;
    }

    /* Custom Switch Toggle styling (Green/Red) */
    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
        margin-bottom: 0;
        vertical-align: middle;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e53e3e; /* Red when unchecked */
        transition: .4s;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
    }
    input:checked + .slider {
        background-color: #38a169; /* Green when checked */
    }
    input:checked + .slider:before {
        transform: translateX(22px);
    }
    .slider.round {
        border-radius: 24px;
    }
    .slider.round:before {
        border-radius: 50%;
    }

    /* Actions styling (Blue circle edit button, Red circle delete button) */
    .action-btn-container {
        display: flex;
        align-items: center;
    }
    .btn-circle-edit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 1px solid #5ac8fa !important;
        color: #5ac8fa !important;
        background-color: transparent !important;
        transition: all 0.2s ease;
    }
    .btn-circle-edit:hover {
        background-color: #5ac8fa !important;
        color: #fff !important;
        text-decoration: none;
    }
    .btn-circle-edit i {
        font-size: 16px;
    }
    .btn-circle-delete {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 1px solid #ff3b30 !important;
        color: #ff3b30 !important;
        background-color: transparent !important;
        transition: all 0.2s ease;
    }
    .btn-circle-delete:hover {
        background-color: #ff3b30 !important;
        color: #fff !important;
        text-decoration: none;
    }
    .btn-circle-delete i {
        font-size: 16px;
    }

    /* Create Banner Button styling */
    .btn-create-banner {
        background-color: #000000 !important;
        color: #ffffff !important;
        border-radius: 30px !important;
        padding: 8px 20px !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        border: none !important;
        display: inline-flex;
        align-items: center;
        transition: background-color 0.2s ease;
    }
    .btn-create-banner:hover {
        background-color: #222 !important;
        color: #fff !important;
        text-decoration: none;
    }

    /* Customizing DataTables elements */
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 6px 15px;
        font-size: 13px;
        outline: none;
        transition: border-color 0.2s;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #cbd5e1;
    }
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 4px 8px;
        outline: none;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #000 !important;
        color: #fff !important;
        border: 1px solid #000 !important;
        border-radius: 50% !important;
        width: 32px !important;
        height: 32px !important;
        padding: 0 !important;
        line-height: 30px !important;
        text-align: center !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 50% !important;
        width: 32px !important;
        height: 32px !important;
        padding: 0 !important;
        line-height: 30px !important;
        text-align: center !important;
        margin: 0 3px !important;
        border: 1px solid transparent !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f1f5f9 !important;
        color: #000 !important;
        border: 1px solid #cbd5e1 !important;
    }

    /* Checkbox & Delete All Styling */
    .delete-all input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #ff5e3a;
    }
    .delete-all label {
        margin-bottom: 0;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }
    .is_open {
        width: 18px !important;
        height: 18px !important;
        cursor: pointer;
        accent-color: #ff5e3a;
    }
</style>
@endsection

@section('scripts')

<script type="text/javascript">

    var section_id = getCookie('section_id') || '';
    var user_permissions = '<?php echo @session('user_permissions') ?>';
    user_permissions = JSON.parse(user_permissions);
    var checkDeletePermission = false;
    if ($.inArray('banners.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
    }
    var database = kweekDb();
    var offest = 1;
    var pagesize = 10;
    var end = null;
    var endarray = [];
    var start = null;
    var user_number = [];
    var refData = database.collection('banner_items');

    if(section_id){
        refData = refData.where('sectionId', '==', section_id);
    }
    
    var append_list = '';
    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    placeholder.get().then(async function (snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })
    
    $(document).ready(function () {
            jQuery("#data-table_processing").show();
            append_list = document.getElementById('append_vendors');
            append_list.innerHTML = '';
            refData.get().then(async function (snapshots) {
                html = '';
                html = await buildHTML(snapshots);
                $(function () {
                    $('[data-toggle="tooltip"]').tooltip();
                });
                jQuery("#data-table_processing").hide();
                if (html != '') {
                    append_list.innerHTML = html;
                    start = snapshots.docs[snapshots.docs.length - 1];
                    endarray.push(snapshots.docs[0]);
                    if (snapshots.docs.length < pagesize) {
                        jQuery("#data-table_paginate").hide();
                    }
                }
                var table =  $('#example24').DataTable({
                    order: [],
                    columnDefs: [{
                             targets: (checkDeletePermission==true) ? 3 : 2,
                             type: 'date',
                            render: function(data) {
                                return data;
                            }
                        },
                        {orderable: false, targets: (checkDeletePermission==true) ? [0,3, 4] : [0,2,3]},
                    ],
                    order: (checkDeletePermission==true) ? [1,"asc"] : [0,"asc"],
                    "language": {
                        "zeroRecords": "{{trans("lang.no_record_found")}}",
                        "emptyTable": "{{trans("lang.no_record_found")}}"
                    }
                });
                table.on('search.dt', function() {
                    var filteredCount = table.rows({ search: 'applied' }).count();
                    $('.total_count').text(filteredCount);  // Update count
                });
            });
    })
    async function buildHTML(snapshots) {
        var html = '';
        if (snapshots.docs.length > 0) {
            $('.total_count').text(snapshots.docs.length); 
        }
        else
        {
            $('.total_count').text(0); 
        }
        await Promise.all(snapshots.docs.map(async (listval) => {
            var val = listval.data();
            var getData = await getListData(val);
            html += getData;
        }));
        return html;
    }
    async function getListData(val) {
        var html = '';
        var number = [];
        var count = 0;
            html = html + '<tr>';
            newdate = '';
            var id = val.id;
            var route1 = '{{route("banners.edit",":id")}}';
            route1 = route1.replace(':id', id);
            if(checkDeletePermission){
                html = html + '<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label for="is_open_' + id + '" ></label></td>';
            }
            
            var photoUrl = val.photo || placeholderImage;
            html = html + '<td>' +
                '<div class="banner-info-container">' +
                    '<img class="banner-img" src="' + photoUrl + '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">' +
                    '<a href="' + route1 + '" class="banner-name-link">' + val.title + '</a>' +
                '</div>' +
            '</td>';
            
            html = html + '<td>' + val.position + '</td>';
            
            var isChecked = val.is_publish ? 'checked' : '';
            html = html + '<td>' +
                '<label class="switch">' +
                    '<input type="checkbox" ' + isChecked + ' id="' + val.id + '" name="isSwitch">' +
                    '<span class="slider round"></span>' +
                '</label>' +
            '</td>';
            
            html = html + '<td>' +
                '<div class="action-btn-container">' +
                    '<a href="' + route1 + '" class="btn-circle-edit" data-toggle="tooltip" title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
            if(checkDeletePermission){
                html = html + '<a id="' + val.id + '" name="vendor-delete" class="btn-circle-delete ml-2" href="javascript:void(0)" data-toggle="tooltip" title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
            }
            html = html + '</div></td>';
            html = html + '</tr>';
            count = count + 1;
            return html;
    }
    $(document).on("click","input[name='isSwitch']",function(e){
        var ischeck=$(this).is(':checked');
        var id=this.id;
        if(ischeck){
            database.collection('banner_items').doc(id).update({'is_publish': true}).then(function (result) {
            });
        }else{
            database.collection('banner_items').doc(id).update({'is_publish': false}).then(function (result) {
            });
        }
    });
    async function getSectionName(sectionId) {
        var refData = await database.collection('sections').doc(sectionId).get();
        var data = refData.data();
        return data.name;
    }
    $("#is_active").click(function () {
        $("#example24 .is_open").prop('checked', $(this).prop('checked'));
    });
    $("#deleteAll").click(function () {
        if ($('#example24 .is_open:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                $('#example24 .is_open:checked').each(async function () {
                    var dataId = $(this).attr('dataId');
                    await deleteDocumentWithImage('banner_items',dataId,'photo');
                    window.location.reload();
                });
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });
    $(document).on("click", "a[name='vendor-delete']", async function (e) {
        var id = this.id;
        await deleteDocumentWithImage('banner_items',id,'photo');
        window.location.reload();
    });
</script>
@endsection
