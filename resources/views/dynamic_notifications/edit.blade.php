@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor" style="font-weight: bold; color:#000;">Edit Notification</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dynamic-notifications.index') }}">App Notification</a></li>
                <li class="breadcrumb-item active" style="color: #9ca3af;">Edit Notification</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-md-10">
                <form method="POST" action="{{ route('dynamic-notifications.update', $record->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="service_type" value="{{ $record->service_type }}">
                    <input type="hidden" name="type" value="{{ $record->type }}">

                    <div class="card border">
                        <div class="card-body">
                            <div class="mb-4">
                                <span class="badge" style="background-color: #000; color: #fff; padding: 6px 12px; font-weight: 700; font-size: 11px; border-radius: 4px; letter-spacing: 0.5px;">NOTIFICATION</span>
                            </div>

                            <div class="form-group mb-4">
                                <label class="control-label" style="font-weight: 600; color: #2b354e; margin-bottom: 6px; font-size: 14px;">Type</label>
                                <input type="text" class="form-control" value="{{ ucwords(str_replace(['_', '-'], ' ', $record->type)) }}" readonly style="background-color: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; border-radius: 4px; padding: 10px 14px; font-size: 14px;">
                            </div>

                            <div class="form-group mb-4">
                                <label class="control-label" style="font-weight: 600; color: #2b354e; margin-bottom: 6px; font-size: 14px;">Subject</label>
                                <input type="text" name="subject" class="form-control" value="{{ old('subject', $record->subject) }}" style="border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px 14px; font-size: 14px;">
                            </div>

                            <div class="form-group mb-2">
                                <label class="control-label" style="font-weight: 600; color: #2b354e; margin-bottom: 6px; font-size: 14px;">Message</label>
                                <textarea name="message" class="form-control" rows="4" style="border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px 14px; font-size: 14px;">{{ old('message', $record->message) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center mt-4" style="gap: 12px;">
                        <button type="submit" class="btn btn-dark" style="background-color: #000; border-color: #000; border-radius: 4px; padding: 8px 24px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; color: #fff; font-size: 14px;">
                            <i class="fa fa-save"></i> Save
                        </button>
                        <a href="{{ route('dynamic-notifications.index') }}" class="btn btn-secondary" style="background-color: #9ca3af; border-color: #9ca3af; border-radius: 4px; padding: 8px 24px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; color: #fff; font-size: 14px;">
                            <i class="fa fa-undo"></i> Back
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
