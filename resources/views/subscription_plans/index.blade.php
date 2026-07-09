@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor restaurantTitle">{{ trans('lang.subscription_plans') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.subscription_plans') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <!-- Admin Top Section -->
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-items-center">
                            <span class="icon mr-3"><img src="{{ asset('images/subscription.png') }}"></span>
                            <h3 class="mb-0">{{ trans('lang.subscription_plans') }}</h3>
                            <span class="counter ml-3 total_count"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overview Section -->
        <div class="overview-sec">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title d-flex">
                                <div>
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.overview') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.see_overview_of_package_earning') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row subscription-list">
                                <!-- Cards will be dynamically loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription Package Lists Section -->
        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.subscription_package_list') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.manage_all_package_in_single_click') }}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a href="{{ route('subscription-plans.create') }}" class="btn-primary btn rounded-full"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.create_subscription_plan') }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="subscriptionPlansTable" class="display nowrap table table-hover table-striped table-bordered table table-striped dataTable no-footer dtr-inline collapsed" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i>All</a></label></th>
                                            @foreach($columns as $column)
                                                <th>{{ $column['label'] }}</th>
                                            @endforeach
                                            <th>{{ trans('lang.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
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
@include('admin.partials.crud-scripts', ['mode' => 'index', 'tableId' => 'subscriptionPlansTable'])

<script>
    var database = kweekDb();
    var section_id = getCookie('section_id') || null;

    $(document).ready(function() {
        loadOverviewCards();
        loadTotalCount();
    });

    function loadTotalCount() {
        if (!section_id) return;

        database.collection('subscription_plans')
            .where('sectionId', '==', section_id)
            .get()
            .then(function(snapshot) {
                $('.total_count').text(snapshot.size);
            })
            .catch(function(error) {
                console.error('Error loading total count:', error);
            });
    }

    function loadOverviewCards() {
        if (!section_id) return;

        database.collection('subscription_plans')
            .where('sectionId', '==', section_id)
            .where('isEnable', '==', true)
            .limit(4)
            .get()
            .then(function(snapshot) {
                var cardsHtml = '';
                snapshot.forEach(function(doc) {
                    var data = doc.data();
                    var badgeClass = data.type === 'paid' ? 'badge-primary' : 'badge-success';
                    var badgeText = data.type === 'paid' ? 'Premium' : 'Basic';
                    var planCode = data.id ? data.id.substring(0, 6) : 'P' + Math.floor(Math.random() * 10000);
                    
                    cardsHtml += `
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="mb-1">${planCode}</h5>
                                            <h6 class="text-muted mb-0">${data.name || 'Plan'}</h6>
                                        </div>
                                        <span class="badge ${badgeClass}">${badgeText}</span>
                                    </div>
                                    <h4 class="text-primary mb-0">${data.price > 0 ? '$' + data.price : 'Free'}</h4>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                if (cardsHtml === '') {
                    cardsHtml = '<div class="col-12"><p class="text-muted">{{ trans("lang.no_active_plans") }}</p></div>';
                }
                
                $('.subscription-list').html(cardsHtml);
            })
            .catch(function(error) {
                console.error('Error loading overview cards:', error);
            });
    }
</script>
@endsection
