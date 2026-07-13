@extends('layouts.app')

@section('content')

<div class="page-wrapper">

    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.parcel_weight')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>

                <li class="breadcrumb-item active">{{trans('lang.parcel_weight')}}</li>

            </ol>

        </div>

        <div>

        </div>

    </div>


    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="card-body">
                        <div class="resttab-sec">

                            <div class="error_top"></div>
                            <div class="row vendor_payout_create">
                                <div class="vendor_payout_create-inner">
                                    <fieldset>
                                        <legend>{{trans('lang.parcel_weight')}}</legend>

                                        <div class="form-group row">

                                            <div class="special_offer_div">

                                                <div class="form-group row">
                                                    <label class="col-12 control-label"
                                                           style="color:red;font-size:15px;">{{trans('lang.edit_button_save_note')}} </label>
                                                </div>
                                                <?php if (in_array('parcel.weight.create', json_decode(@session('user_permissions')))) { ?>
                                                    <div class="form-group row">

                                                        <div class="col-12">
                                                            <button type="button"
                                                                    class="btn btn-primary add_more_sunday"
                                                                    onclick="addMoreButton()">{{trans('lang.add_more')}}
                                                            </button>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                                <div class="parcel_weight" style="display:none">
                                                    <table class="booking-table" id="parcel_weight_table">
                                                        <tr>
                                                            <th style="width:50%"><label class="col-3 control-label">{{trans('lang.title')}}</label>
                                                            </th>
                                                            <th style="width:40%"><label class="col-3 control-label">{{trans('lang.delivery_charge')}}</label>
                                                            </th>

                                                            <?php if (in_array('parcel.weight.edit', json_decode(@session('user_permissions'))) || in_array('parcel.weight.delete', json_decode(@session('user_permissions')))) { ?>

                                                                <th style="width:20%"><label
                                                                            class="col-3 control-label">{{trans('lang.actions')}}</label>
                                                                </th>
                                                            <?php } ?>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                    </fieldset>

                                    <div class="form-group col-12 text-center btm-btn">
                                        <button type="button" class="btn btn-primary  save-form-btn"><i
                                                    class="fa fa-save"></i> {{trans('lang.save')}}
                                        </button>
                                        <a href="{!! route('parcel_weight') !!}" class="btn btn-default"><i
                                                    class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
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

    <script type="text/javascript">
        var arrayParcelWeight = [];
        var editCount = 0;
        var countAddButton = 1;
        var csrfToken = '{{ csrf_token() }}';
        var listUrl = "{{ route('parcel_weight.data') }}";
        var saveUrl = "{{ route('parcel_weight.save') }}";
        var bulkSaveUrl = "{{ route('parcel_weight.bulk_save') }}";
        var deleteUrlTemplate = "{{ url('parcel_weight') }}/";

        function generateId() {
            if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                return window.crypto.randomUUID().replace(/-/g, '').slice(0, 20);
            }
            return 'pw_' + Date.now() + '_' + Math.floor(Math.random() * 100000);
        }

        function updateParcelWeightArray(id, title, price) {
            for (var i = 0; i < arrayParcelWeight.length; i++) {
                if (arrayParcelWeight[i]['id'] == id) {
                    arrayParcelWeight[i]['title'] = title;
                    arrayParcelWeight[i]['delivery_charge'] = price;
                    return;
                }
            }

            arrayParcelWeight.push({
                'id': id,
                'title': title,
                'delivery_charge': price
            });
        }

        function replaceText(id) {
            $('.edit_' + id).html("<i class='fa fa-save'></i>");
            updateParcelWeightArray(id, $("#title_" + id).val(), $("#price_" + id).val());
        }

        function renderParcelWeightRow(parcelWeightData) {
            var docId = parcelWeightData.id;
            updateParcelWeightArray(docId, parcelWeightData.title, parcelWeightData.delivery_charge);
            $(".parcel_weight").show();

            var html = '<tr>' +
                '<td style="width:40%"><input type="text" value="' + parcelWeightData.title + '" class="form-control" id="title_' + docId + '" onchange="replaceText(`' + docId + '`)"></td>' +
                '<td style="width:40%"><input type="text" value="' + parcelWeightData.delivery_charge + '" class="form-control" id="price_' + docId + '" onchange="replaceText(`' + docId + '`)"></td>';

            <?php if (in_array('parcel.weight.edit', json_decode(@session('user_permissions'))) || in_array('parcel.weight.delete', json_decode(@session('user_permissions')))) { ?>
            html += '<td class="action-btn" style="width:20%">';
            <?php if (in_array('parcel.weight.edit', json_decode(@session('user_permissions')))){?>
            html += '<span class="edit-form-btn"><button type="button" class="btn btn-primary edit_' + docId + '" onclick="editData(`' + editCount + '`,`' + docId + '`)"><i class="fa fa-edit"></i></button>&nbsp;&nbsp</span>';
            <?php }
            if (in_array('parcel.weight.delete', json_decode(@session('user_permissions')))){
            ?>
            html += '<span class="delete-btn"><button type="button" class="btn btn-primary delete_' + docId + '" onclick="deleteData(`' + docId + '`)"><i class="fa fa-trash"></i></button></span>';
            <?php }?>
            html += '</td>';
            <?php }?>
            html += '</tr>';

            $('#parcel_weight_table tr:last').after(html);
            editCount++;
        }

        function loadParcelWeights() {
            $.ajax({
                url: listUrl,
                method: 'GET',
                dataType: 'json',
                cache: false,
                success: function (response) {
                    arrayParcelWeight = [];
                    editCount = 0;
                    $('#parcel_weight_table tr:not(:first)').remove();

                    (response.data || []).forEach(function (item) {
                        renderParcelWeightRow(item);
                    });
                },
                error: function () {
                    $(".error_top").show().html("<p>Failed to load parcel weights.</p>");
                }
            });
        }

        $(document).ready(function () {
            loadParcelWeights();
        });

        function addMoreButton() {
            var count = countAddButton;
            $(".parcel_weight").show();

            $('#parcel_weight_table tr:last').after('<tr>' +
                '<td><input type="text" class="form-control" id="title_' + count + '"></td>' +
                '<td><input type="text" class="form-control" id="price_' + count + '"></td>' +
                '<td class="action-btn">' +
                '<button type="button" class="btn btn-primary save_' + count + '" onclick="saveData(' + count + ')">Save</button>' +
                '</td></tr>');
            countAddButton++;
        }

        function saveData(count) {
            var title = $("#title_" + count).val();
            var price = $("#price_" + count).val();

            if (title == "") {
                $(".error_top").show().html("<p>{{trans('lang.parcel_title_error')}}</p>");
                window.scrollTo(0, 0);
                return;
            }
            if (price == "") {
                $(".error_top").show().html("<p>{{trans('lang.parcel_price_error')}}</p>");
                window.scrollTo(0, 0);
                return;
            }

            $(".error_top").hide().html("");
            var newId = generateId();
            updateParcelWeightArray(newId, title, price);

            $(".save_" + count).hide();
            $("#title_" + count).attr('id', 'title_' + newId).attr('disabled', true);
            $("#price_" + count).attr('id', 'price_' + newId).attr('disabled', true);
        }

        function editData(count, actionId) {
            if (typeof is_disable_delete !== "undefined" && is_disable_delete == 1) {
                alert(doNotUpdateAlert);
                return false;
            }

            var title = $("#title_" + actionId).val();
            var price = $("#price_" + actionId).val();

            if (title == "") {
                $(".error_top").show().html("<p>{{trans('lang.parcel_title_error')}}</p>");
                window.scrollTo(0, 0);
                return;
            }
            if (price == "") {
                $(".error_top").show().html("<p>{{trans('lang.parcel_price_error')}}</p>");
                window.scrollTo(0, 0);
                return;
            }

            $(".error_top").hide().html("");

            $.ajax({
                url: saveUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    _token: csrfToken,
                    id: actionId,
                    title: title,
                    delivery_charge: price
                },
                success: function () {
                    updateParcelWeightArray(actionId, title, price);
                    $('.edit_' + actionId).html("<i class='fa fa-check'></i>");
                },
                error: function (xhr) {
                    $(".error_top").show().html("<p>" + (xhr.responseJSON?.message || 'Failed to update parcel weight.') + "</p>");
                }
            });
        }

        function deleteData(actionId) {
            if (typeof is_disable_delete !== "undefined" && is_disable_delete == 1) {
                alert(doNotDeleteAlert);
                return false;
            }

            if (!confirm('{{ trans("lang.delete_alert") }}')) {
                return false;
            }

            $.ajax({
                url: deleteUrlTemplate + actionId,
                method: 'POST',
                dataType: 'json',
                data: {
                    _token: csrfToken,
                    _method: 'DELETE'
                },
                success: function () {
                    window.location.href = '{{ route("parcel_weight") }}';
                },
                error: function (xhr) {
                    $(".error_top").show().html("<p>" + (xhr.responseJSON?.message || 'Failed to delete parcel weight.') + "</p>");
                }
            });
        }

        $(document).on('click', '.save-form-btn', function () {
            var items = [];

            for (var i = 0; i < arrayParcelWeight.length; i++) {
                var item = arrayParcelWeight[i];
                var latestTitle = $("#title_" + item['id']).length ? $("#title_" + item['id']).val() : item['title'];
                var latestPrice = $("#price_" + item['id']).length ? $("#price_" + item['id']).val() : item['delivery_charge'];

                updateParcelWeightArray(item['id'], latestTitle, latestPrice);
                items.push({
                    id: item['id'],
                    title: latestTitle,
                    delivery_charge: latestPrice
                });
            }

            $.ajax({
                url: bulkSaveUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    _token: csrfToken,
                    items: items
                },
                success: function () {
                    window.location.href = '{{ route("parcel_weight") }}';
                },
                error: function (xhr) {
                    $(".error_top").show().html("<p>" + (xhr.responseJSON?.message || 'Failed to save parcel weights.') + "</p>");
                }
            });
        });
    </script>

    @endsection
