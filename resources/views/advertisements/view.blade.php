@extends('layouts.app')

@section('content')
@php
    $startDate = $payload['startDate'] ?? null;
    $endDate = $payload['endDate'] ?? null;
    $duration = '—';
    if ($startDate || $endDate) {
        try {
            $from = $startDate ? date('F j, Y', strtotime($startDate)) : '';
            $to = $endDate ? date('F j, Y', strtotime($endDate)) : '';
            $duration = trim($from . ($from && $to ? ' - ' : '') . $to);
        } catch (\Throwable $e) {
            $duration = trim(($startDate ?? '') . ' - ' . ($endDate ?? ''));
        }
    }

    $status = $payload['status'] ?? 'pending';
    $isPaused = ! empty($payload['isPaused']);
    $now = now();
    $running = false;
    if ($status === 'approved' && !$isPaused) {
        $startOk = ! $startDate || strtotime($startDate) <= $now->timestamp;
        $endOk = ! $endDate || strtotime($endDate) >= $now->timestamp;
        $running = $startOk && $endOk;
    }
@endphp

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Advertisement Details</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('advertisements') }}">{{ trans('lang.advertisement_plural') }}</a></li>
                <li class="breadcrumb-item active">Advertisement Details</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-7 col-md-12">
                <!-- General Details Card -->
                <div class="card border-0 mb-4" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                    <div class="card-header bg-white border-0" style="padding: 20px 24px;">
                        <h4 class="mb-0 font-weight-bold" style="color: #2b354e; font-size: 16px;">General Details</h4>
                    </div>
                    <div class="card-body" style="padding: 0 24px 24px 24px;">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr style="border-bottom: 1px dashed #e2e8f0;">
                                    <td style="padding: 12px 0; color: #718096; width: 35%; font-weight: 500;">Id</td>
                                    <td style="padding: 12px 0; font-weight: 600; color: #2d3748;">{{ $ad->id }}</td>
                                </tr>
                                <tr style="border-bottom: 1px dashed #e2e8f0;">
                                    <td style="padding: 12px 0; color: #718096; font-weight: 500;">Date Created</td>
                                    <td style="padding: 12px 0; font-weight: 600; color: #2d3748;">{{ !empty($ad->created_at) ? \Carbon\Carbon::parse($ad->created_at)->format('Y-m-d h:i:s A') : '—' }}</td>
                                </tr>
                                <tr style="border-bottom: 1px dashed #e2e8f0;">
                                    <td style="padding: 12px 0; color: #718096; font-weight: 500;">Advertisement Type</td>
                                    <td style="padding: 12px 0; font-weight: 600; color: #2d3748;">
                                        {{ $payload['type'] === 'video_promotion' ? 'Video Promotion' : ($payload['type'] === 'restaurant_promotion' ? 'Store Promotion' : ($payload['type'] ?? '—')) }}
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px dashed #e2e8f0;">
                                    <td style="padding: 12px 0; color: #718096; font-weight: 500;">Duration</td>
                                    <td style="padding: 12px 0; font-weight: 600; color: #2d3748;">{{ $duration }}</td>
                                </tr>
                                <tr style="border-bottom: 1px dashed #e2e8f0;">
                                    <td style="padding: 12px 0; color: #718096; font-weight: 500;">Payment Status</td>
                                    <td style="padding: 12px 0;">
                                        @if(!empty($payload['paymentStatus']))
                                            <span class="badge rounded-pill px-3 py-1 payment-badge" style="background-color: #e6fcf5; color: #20c997; font-size: 12px; font-weight: 600;">Paid</span>
                                        @else
                                            <span class="badge rounded-pill px-3 py-1 payment-badge" style="background-color: #fff5f5; color: #f03e3e; font-size: 12px; font-weight: 600;">Unpaid</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 0; color: #718096; font-weight: 500;">Status</td>
                                    <td style="padding: 12px 0;">
                                        @if($isPaused)
                                            <span class="badge badge-warning rounded-pill px-3 py-1" style="background-color: #ffefeb; color: #ff3b30; font-size: 12px; font-weight: 600;">Paused</span>
                                        @elseif($running)
                                            <span class="badge badge-info rounded-pill px-3 py-1" style="background-color: #e6fcf5; color: #20c997; font-size: 12px; font-weight: 600;">Running</span>
                                        @elseif($status === 'approved')
                                            <span class="badge badge-success rounded-pill px-3 py-1" style="background-color: #e6fcf5; color: #20c997; font-size: 12px; font-weight: 600;">Approved</span>
                                        @elseif($status === 'pending')
                                            <span class="badge badge-secondary rounded-pill px-3 py-1" style="background-color: #f1f3f5; color: #495057; font-size: 12px; font-weight: 600;">Pending</span>
                                        @else
                                            <span class="badge badge-danger rounded-pill px-3 py-1" style="background-color: #fff5f5; color: #f03e3e; font-size: 12px; font-weight: 600;">{{ ucfirst($status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Advertisement Details Card -->
                <div class="card border-0 mb-4" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                    <div class="card-header bg-white border-0" style="padding: 20px 24px;">
                        <h4 class="mb-0 font-weight-bold" style="color: #2b354e; font-size: 16px;">Advertisement Details</h4>
                    </div>
                    <div class="card-body" style="padding: 0 24px 24px 24px;">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr style="border-bottom: 1px dashed #e2e8f0;">
                                    <td style="padding: 12px 0; color: #718096; width: 35%; font-weight: 500;">Title</td>
                                    <td style="padding: 12px 0; font-weight: 600; color: #2d3748;">{{ $ad->title }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 0; color: #718096; font-weight: 500;">Description</td>
                                    <td style="padding: 12px 0; color: #4a5568; line-height: 1.6;">{{ $payload['description'] ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>

                        @if(($payload['type'] ?? '') === 'video_promotion' && !empty($payload['video']))
                            <div class="mt-3">
                                <label class="font-weight-bold mb-2" style="color: #2b354e; font-size: 14px;">Video</label>
                                <video src="{{ $payload['video'] }}" controls class="w-100" style="border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-height: 400px; background-color: #000; outline: none;"></video>
                            </div>
                        @elseif(!empty($payload['coverImage']))
                            <div class="mt-3">
                                <label class="font-weight-bold mb-2" style="color: #2b354e; font-size: 14px;">Image</label>
                                <img src="{{ $payload['coverImage'] }}" alt="Cover Image" class="w-100" style="border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-height: 400px; object-fit: cover;">
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-5 col-md-12">
                <!-- Advertisement Setup Card -->
                <div class="card border-0 mb-4" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                    <div class="card-header bg-white border-0" style="padding: 20px 24px;">
                        <h4 class="mb-0 font-weight-bold" style="color: #2b354e; font-size: 16px;">Advertisement Setup</h4>
                    </div>
                    <div class="card-body" style="padding: 0 24px 24px 24px;">
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 1px dashed #e2e8f0;">
                            <span style="font-weight: 500; color: #718096;">Payment Status</span>
                            <label class="switch mr-0">
                                <input type="checkbox" id="payment_status_switch" {{ !empty($payload['paymentStatus']) ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold mb-2" style="color: #2b354e; font-size: 14px;">Validity</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px; border-color: #cbd5e0; color: #718096;"><i class="fa fa-calendar"></i></span>
                                </div>
                                <input type="text" class="form-control bg-white border-left-0" readonly value="{{ $duration }}" style="border-radius: 0 8px 8px 0; border-color: #cbd5e0; height: auto; padding: 10px 12px; color: #2b354e; font-size: 14px;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Store Info Card -->
                <div class="card border-0 mb-4" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                    <div class="card-header bg-white border-0" style="padding: 20px 24px;">
                        <h4 class="mb-0 font-weight-bold" style="color: #2b354e; font-size: 16px;">Store Info</h4>
                    </div>
                    <div class="card-body" style="padding: 0 24px 24px 24px;">
                        @if($vendor)
                            <div class="d-flex align-items-center mb-4">
                                <img src="{{ $vendor->photo ?: asset('images/default_user.png') }}" class="rounded-circle mr-3" style="width: 60px; height: 60px; object-fit: cover;" onerror="this.src='{{ asset('images/default_user.png') }}'">
                                <h5 class="mb-0 font-weight-bold" style="color: #2b354e; font-size: 16px;">{{ $vendor->title }}</h5>
                            </div>
                            <div class="pt-2" style="border-top: 1px dashed #e2e8f0;">
                                <table class="table table-borderless mb-0">
                                    <tbody>
                                        <tr style="border-bottom: 1px dashed #e2e8f0;">
                                            <td style="padding: 12px 0; color: #718096; width: 30%; font-weight: 500;">Phone</td>
                                            <td style="padding: 12px 0; font-weight: 600; color: #2d3748;">{{ $vendor->phonenumber ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; color: #718096; font-weight: 500;">Address</td>
                                            <td style="padding: 12px 0; color: #4a5568; line-height: 1.5; font-size: 14px;">{{ $vendor->location ?? '—' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No vendor assigned.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="{{ route('advertisements') }}" class="btn btn-secondary px-5 py-2 font-weight-bold" style="background-color: #cbd5e0; border-color: #cbd5e0; color: #4a5568; border-radius: 8px;">
                    <i class="fa fa-undo mr-1"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
/* Custom Toggle Switch Styles */
.switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 24px;
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
    background-color: #cbd5e0;
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
    transition: .4s;
}
input:checked + .slider {
    background-color: #20c997;
}
input:checked + .slider:before {
    transform: translateX(24px);
}
.slider.round {
    border-radius: 24px;
}
.slider.round:before {
    border-radius: 50%;
}
</style>
<script>
$(document).ready(function () {
    $('#payment_status_switch').on('change', function () {
        var status = $(this).is(':checked') ? 1 : 0;
        var adId = "{{ $ad->id }}";
        
        $.post("{{ route('advertisements.toggle-payment') }}", {
            _token: '{{ csrf_token() }}',
            id: adId,
            status: status
        }).done(function (res) {
            if (res.success) {
                // Update badge text and style
                var badge = $('.payment-badge');
                if (status === 1) {
                    badge.text('Paid').css({
                        'background-color': '#e6fcf5',
                        'color': '#20c997'
                    });
                } else {
                    badge.text('Unpaid').css({
                        'background-color': '#fff5f5',
                        'color': '#f03e3e'
                    });
                }
            } else {
                alert('Failed to update payment status.');
            }
        }).fail(function () {
            alert('An error occurred. Please try again.');
        });
    });
});
</script>
@endsection
