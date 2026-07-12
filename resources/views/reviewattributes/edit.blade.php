@extends('layouts.app')

@section('style')
<style>
    /* Card and container styling */
    .vendor_payout_create {
        background: transparent !important;
        padding: 0 !important;
    }
    .vendor_payout_create-inner {
        width: 100% !important;
        max-width: 100% !important;
    }

    /* Fieldset styling */
    .vendor_payout_create fieldset {
        border: 1.5px solid #eaeaea !important;
        padding: 24px !important;
        margin-bottom: 30px !important;
        border-radius: 6px !important;
        background-color: #fff !important;
        position: relative !important;
    }

    /* Legend styling */
    .vendor_payout_create legend {
        background-color: #000 !important;
        color: #fff !important;
        padding: 6px 14px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        width: auto !important;
        margin-bottom: 25px !important;
        border: none !important;
        border-radius: 0 !important;
        display: inline-block !important;
        float: none !important;
    }

    /* Form layout - stack them vertically */
    .vendor_payout_create .form-group {
        display: flex !important;
        flex-direction: column !important;
        margin-bottom: 20px !important;
        width: 100% !important;
        float: none !important;
        padding: 0 !important;
    }

    /* Control labels */
    .vendor_payout_create .control-label {
        font-size: 14px !important;
        font-weight: 600 !important;
        color: #1a202c !important;
        margin-bottom: 8px !important;
        text-align: left !important;
        width: 100% !important;
        max-width: 100% !important;
        flex: none !important;
    }

    /* Text inputs and Textareas */
    .vendor_payout_create .form-control {
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        padding: 10px 14px !important;
        border: 1px solid #cbd5e0 !important;
        border-radius: 4px !important;
        font-size: 14px !important;
        color: #2d3748 !important;
        background-color: #fff !important;
        box-shadow: none !important;
        transition: border-color 0.15s ease-in-out !important;
    }
    .vendor_payout_create .form-control:focus {
        border-color: #3182ce !important;
        outline: none !important;
    }

    /* Button section styling */
    .btm-btn {
        text-align: center !important;
        margin-top: 20px !important;
        margin-bottom: 40px !important;
    }
    .save-form-btn, .edit-form-btn {
        background-color: #000 !important;
        border-color: #000 !important;
        color: #fff !important;
        border-radius: 4px !important;
        padding: 8px 24px !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        transition: all 0.2s ease !important;
        box-shadow: none !important;
    }
    .save-form-btn:hover, .edit-form-btn:hover {
        background-color: #333 !important;
        border-color: #333 !important;
    }
    .btn-default {
        background-color: #718096 !important;
        border-color: #718096 !important;
        color: #fff !important;
        border-radius: 4px !important;
        padding: 8px 24px !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        margin-left: 10px !important;
        transition: all 0.2s ease !important;
    }
    .btn-default:hover {
        background-color: #4a5568 !important;
        border-color: #4a5568 !important;
        color: #fff !important;
    }
</style>
@endsection

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.reviewattribute_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('reviewattributes') }}">{{ trans('lang.reviewattribute_plural') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.reviewattribute_edit') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card pb-4" style="border-radius: 6px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
            <div class="card-body">
                <div class="error_top" style="display:none"></div>
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('reviewattributes.update', $id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row vendor_payout_create">
                        <div class="vendor_payout_create-inner">
                            <fieldset>
                                <legend>{{ trans('lang.reviewattribute_edit') }}</legend>
                                <div class="form-group">
                                    <label class="control-label">{{ trans('lang.reviewattribute_name') }}</label>
                                    <input type="text" name="title"
                                        class="form-control reviewattribute-name @error('title') is-invalid @enderror"
                                        value="{{ old('title', $record->title ?? $record->name ?? '') }}" required autofocus>
                                    <div class="form-text text-muted mt-1">{{ trans('lang.reviewattribute_name_help') }}</div>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <div class="form-group col-12 text-center btm-btn">
                        <button type="submit" class="btn btn-primary edit-form-btn">
                            <i class="fa fa-save"></i> {{ trans('lang.save') }}
                        </button>
                        <a href="{{ route('reviewattributes') }}" class="btn btn-default">
                            <i class="fa fa-undo"></i> {{ trans('lang.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
  var database = kweekDb();
  var id = "<?php echo $id; ?>";
  var ref = database.collection('review_attributes').where("id", "==", id);

  $(document).ready(function () {
    jQuery("#data-table_processing").show();
    ref.get().then(async function (snapshots) {
      if (snapshots.docs.length > 0) {
        var data = snapshots.docs[0].data();
        $(".reviewattribute-name").val(data.title);
      }
      jQuery("#data-table_processing").hide();
    });

    $(".edit-form-btn").click(function () {
      var title = $(".reviewattribute-name").val();

      if (title == '') {
        $(".error_top").show();
        $(".error_top").html("");
        $(".error_top").append("<p>{{trans('lang.enter_cat_title_error')}}</p>");
        window.scrollTo(0, 0);
      } else {
        database.collection('review_attributes').doc(id).update({ 'title': title }).then(function () {
          window.location.href = '{{ url("reviewattributes") }}';
        });
      }
    });
  });
</script>
@endsection
