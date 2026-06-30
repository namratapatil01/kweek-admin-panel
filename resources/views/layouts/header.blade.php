@php
$user = Auth::user();
$role_has_permission = App\Models\Permission::where('role_id', $user->role_id)->pluck('permission')->toArray();
$isSuperAdmin = (int) $user->role_id === 1;
if ($isSuperAdmin && empty($role_has_permission)) {
    $role_has_permission = DB::table('permissions')->where('role_id', 1)->pluck('permission')->toArray();
}
$service_type = @$_COOKIE['service_type'];
if (empty($service_type) || $service_type == 'undefined') {
    $service_type = request()->route('type');
}
if (empty($service_type)) {
    if (request()->is('providers*') || request()->is('ondemand-*') || request()->is('workers*') || request()->is('bookings*') || request()->is('provider*')) {
        $service_type = 'ondemand-service';
    } elseif (request()->is('rentalvehicle*') || request()->is('rental-package*') || request()->is('rentaldiscount*') || request()->is('rental_orders*') || request()->is('carMake*') || request()->is('carModel*')) {
        $service_type = 'rental-service';
    } elseif (request()->is('rides*') || request()->is('cab-service*')) {
        $service_type = 'cab-service';
    } elseif (request()->is('parcel*')) {
        $service_type = 'parcel_delivery';
    }
}
@endphp


<div class="navbar-header position-relative">
    <a class="navbar-brand" href="<?php echo URL::to('/'); ?>">
        <b>
            <img src="{{ asset('images/kweek_icon.png') }}" onerror="this.onerror=null; this.src='{{ asset('images/logo_web.png') }}';" alt="KWEEK" class="dark-logo" width="100%" id="logo_web">
            <img src="{{ asset('images/kweek_icon.png') }}" onerror="this.onerror=null; this.src='{{ asset('images/logo-light-icon.png') }}';" alt="KWEEK" class="light-logo">
        </b>
    </a>
    <div class="sidebar-toggle">  
        <span class="nav-item mb-toggle">
            <a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="mdi mdi-menu"></i></a>
        </span>
        <span class="nav-item">
            <a class="nav-link sidebartoggler hidden-sm-down text-muted waves-effect waves-dark" href="javascript:void(0)">
                <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 19 19" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M0.791687 9.49999C0.791687 4.69051 4.69054 0.791656 9.50002 0.791656C14.3095 0.791656 18.2084 4.69051 18.2084 9.49999C18.2084 14.3095 14.3095 18.2083 9.50002 18.2083C4.69054 18.2083 0.791687 14.3095 0.791687 9.49999ZM9.50002 2.37499C5.56499 2.37499 2.37502 5.56496 2.37502 9.49999C2.37502 13.435 5.56499 16.625 9.50002 16.625C13.4351 16.625 16.625 13.435 16.625 9.49999C16.625 5.56496 13.4351 2.37499 9.50002 2.37499ZM9.85861 5.57561C10.1678 5.88478 10.1678 6.38603 9.85861 6.6952L7.64757 8.90624H12.8613C13.2985 8.90624 13.653 9.26068 13.653 9.69791C13.653 10.1351 13.2985 10.4896 12.8613 10.4896H7.64757L9.85861 12.7006C10.1678 13.0098 10.1678 13.511 9.85861 13.8202C9.54945 14.1294 9.04819 14.1294 8.73903 13.8202L5.17653 10.2577C4.86736 9.94853 4.86736 9.44728 5.17653 9.13811L8.73903 5.57561C9.04819 5.26645 9.54945 5.26645 9.85861 5.57561Z"
                        fill="white"></path>
                </svg>
            </a>
        </span>
    </div>
</div>
<div class="navbar-collapse sidebar-nav">
    <ul class="navbar-nav mr-auto mt-md-0 header-user-menu sidebarnav p-l-20">
        @if ($isSuperAdmin || in_array('users', $role_has_permission))
        <li class="nav-item text-light">
           <a class="nav-link" href="{!! url('users') !!}" aria-expanded="false">
                <i class="mdi mdi-account-multiple"></i>
                <span class="hide-menu">{{ trans('lang.user_customer') }}</span>
            </a>
        </li>
        @endif
        @if ($isSuperAdmin || in_array('zone', $role_has_permission))
        <li class="nav-item text-light">
            <a class="nav-link" href="{!! url('zone') !!}" aria-expanded="false">
                <i class="mdi mdi-map-marker-circle"></i>
                <span class="hide-menu">{{ trans('lang.zone') }}</span>
            </a>
        </li>
        @endif
        @if ($isSuperAdmin || in_array('section-service', $role_has_permission))
        <li class="nav-item text-light">
           <a class="nav-link" href="{!! route('sections.index') !!}" aria-expanded="false">
                <i class="mdi mdi-clipboard-text"></i>
                <span class="hide-menu">{{ trans('lang.section_plural') }}</span>
            </a>
        </li>
        @endif
        @if (
        $isSuperAdmin ||
        in_array('app-banners-setting', $role_has_permission) ||
        in_array('global-setting', $role_has_permission) ||
        in_array('currency', $role_has_permission) ||
        in_array('payment-method', $role_has_permission) ||
        in_array('business-model', $role_has_permission) ||
        in_array('radius', $role_has_permission) ||
        in_array('scheduleOrderNotification', $role_has_permission) ||
        in_array('tax', $role_has_permission) ||
        in_array('delivery-charge', $role_has_permission) ||
        in_array('document-verification', $role_has_permission) ||
        in_array('language', $role_has_permission) ||
        in_array('special-offer', $role_has_permission) ||
        in_array('terms', $role_has_permission) ||
        in_array('privacy', $role_has_permission) ||
        in_array('home-page', $role_has_permission) ||
        in_array('footer', $role_has_permission) || in_array('settings-maintenance', $role_has_permission)
        )
        <li id="activeSection" class="text-light nav-item dropdown">
                <a class="nav-link dropdown-toggle waves-effect waves-dark" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-settings"></i>
                    <span class="hide-menu">{{ trans('lang.app_setting') }}</span>
                </a>
              <div class="dropdown-menu dropdown-setting scale-down sidebar-nav">  
                <ul class="sidebarnav">
                    @if ($isSuperAdmin || in_array('global-setting', $role_has_permission))
                    <li><a class="nav-link"href="{!! url('settings/app/globals') !!}"><i class="mdi mdi-web"></i> {{ trans('lang.app_setting_globals') }}</a></li>
                    @endif
                    @if ($isSuperAdmin || in_array('currency', $role_has_permission))
                    <li><a class="nav-link"href="{!! url('settings/currencies') !!}"><i class="mdi mdi-currency-usd"></i> {{ trans('lang.currency_plural') }}</a></li>
                    @endif
                    @if ($isSuperAdmin || in_array('business-model', $role_has_permission))
                    <li><a class="nav-link"href="{!! url('settings/app/businessModel') !!}"><i class="mdi mdi-domain"></i> {{ trans('lang.business_model_settings') }}</a></li>
                    @endif
                    @if ($isSuperAdmin || in_array('tax', $role_has_permission))
                    <li><a class="nav-link"href="{!! url('tax') !!}"><i class="mdi mdi-database"></i> {{ trans('lang.tax_setting') }}</a></li>
                    @endif
                    @if ($isSuperAdmin || in_array('payment-method', $role_has_permission))
                    <li><a class="nav-link"href="{!! url('settings/payment/stripe') !!}"><i class="mdi mdi-credit-card"></i> {{ trans('lang.app_setting_payment') }}</a></li>
                    @endif
                    @if ($isSuperAdmin || in_array('radius', $role_has_permission))
                    <li><a class="nav-link"href="{!! url('settings/app/radiusConfiguration') !!}"><i class="mdi mdi-map-marker-radius"></i> {{ trans('lang.radios_configuration') }}</a></li>
                    @endif
                    @if ($isSuperAdmin || in_array('app-banners-setting', $role_has_permission))
                    <li><a class="nav-link"href="{!! url('settings/app/banners') !!}"><i class="mdi mdi-page-layout-body"></i> {{ trans('lang.app_setting_banners') }}</a></li>
                    @endif
                    @if ($isSuperAdmin || in_array('scheduleOrderNotification', $role_has_permission))
                    <li><a class="nav-link"href="{!! url('settings/app/scheduleOrderNotification') !!}"><i class="mdi mdi-bell-outline"></i> {{ trans('lang.schedule_order_notification_title') }}</a></li>
                    @endif
                    @if ($isSuperAdmin || in_array('delivery-charge', $role_has_permission))
                    <li><a class="nav-link"href="{!! url('settings/app/deliveryCharge') !!}"><i class="mdi mdi-truck-delivery"></i> {{ trans('lang.delivery_charge') }}</a></li>
                    @endif
                    @if ($isSuperAdmin || in_array('document-verification', $role_has_permission))
                    <li><a class="nav-link"href="{!! url('settings/app/documentVerification') !!}"><i class="mdi mdi-file-document"></i> {{ trans('lang.document_verification') }}</a></li>
                    @endif
                    @if ($isSuperAdmin || in_array('language', $role_has_permission))
                    <li><a class="nav-link"href="{!! url('settings/app/languages') !!}"><i class="mdi mdi-translate"></i> {{ trans('lang.languages') }}</a></li>
                    @endif
                    @if ($isSuperAdmin || in_array('special-offer', $role_has_permission))
                    <li><a class="nav-link"href="{!! url('settings/app/specialOffer') !!}"><i class="mdi mdi-percent"></i> {{ trans('lang.special_offer') }}</a></li>
                    @endif
                    @if ($isSuperAdmin || in_array('settings-maintenance', $role_has_permission))
                    <li><a class="nav-link"href="{!! url('settings/app/maintenance') !!}"><i class="mdi mdi-settings"></i> {{ trans('lang.maintenance_mode_settings') }}</a></li>
                    @endif
                    @if ($isSuperAdmin || in_array('terms', $role_has_permission))
                    <li><a class="nav-link"href="{!! url('termsAndConditions') !!}"><i class="mdi mdi-file"></i> {{ trans('lang.terms_and_conditions') }}</a></li>
                    @endif
                    @if ($isSuperAdmin || in_array('privacy', $role_has_permission))
                    <li><a class="nav-link"href="{!! url('privacyPolicy') !!}"><i class="mdi mdi-file-check"></i> {{ trans('lang.privacy_policy') }}</a></li>
                    @endif
                    @if ($isSuperAdmin || in_array('home-page', $role_has_permission))
                    <li><a class="nav-link"href="{!! url('homepageTemplate') !!}"><i class="mdi mdi-page-layout-body"></i> {{ trans('lang.homepageTemplate') }}</a></li>
                    @endif
                    @if ($isSuperAdmin || in_array('footer', $role_has_permission))
                    <li><a class="nav-link"href="{!! url('footerTemplate') !!}"><i class="mdi mdi-page-layout-footer"></i> {{ trans('lang.footer_template') }}</a></li>
                    @endif
                </ul>
            </div>
        </li>
        @endif

        @if (
        $isSuperAdmin ||
        in_array('banners', $role_has_permission) ||
        in_array('cms', $role_has_permission) ||
        in_array('on-board', $role_has_permission) ||
        in_array('email-template', $role_has_permission)
        )
        <li id="activeSection" class="text-light nav-item dropdown">
            <a class="nav-link dropdown-toggle waves-effect waves-dark" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="mdi mdi-palette"></i>
                <span class="hide-menu">{{ trans('lang.content_management') }}</span>
            </a>
            <div class="dropdown-menu dropdown-setting scale-down sidebar-nav">  
                <ul class="sidebarnav">
                    @if ($isSuperAdmin || in_array('banners', $role_has_permission))
                    <li><a class="waves-effect waves-dark nav-link" href="{!! url('banners') !!}" aria-expanded="false">
                            <i class="mdi mdi-monitor-multiple "></i>
                            <span class="hide-menu">{{ trans('lang.menu_items') }}</span>
                        </a>
                    </li>
                    @endif

                    @if ($isSuperAdmin || in_array('cms', $role_has_permission))
                    <li><a class="waves-effect waves-dark nav-link" href="{!! url('cms') !!}" aria-expanded="false">
                            <i class="mdi mdi-book-open-page-variant"></i>
                            <span class="hide-menu">{{ trans('lang.cms_plural') }}</span>
                        </a>
                    </li>
                    @endif

                    @if ($isSuperAdmin || in_array('on-board', $role_has_permission))
                    <li><a class="waves-effect waves-dark nav-link onboard_menu" href="{!! url('on-board') !!}" aria-expanded="false">
                            <i class="mdi mdi-cellphone"></i>
                            <span class="hide-menu">{{ trans('lang.on_board_plural') }}</span>
                        </a>
                    </li>
                    @endif

                    @if ($isSuperAdmin || in_array('email-template', $role_has_permission))
                    <li><a class="waves-effect waves-dark nav-link" href="{!! url('email-templates') !!}" aria-expanded="false">
                            <i class="mdi mdi-email"></i>
                            <span class="hide-menu">{{ trans('lang.email_templates') }}</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </li>
        @endif
    </ul>

    <div style="visibility: hidden;" class="language-list icon d-flex align-items-center text-light ml-2" id="language_dropdown_box">
        <div class="language-select">
            <i class="fa fa-globe"></i>
        </div>
        <div class="language-options">
            <select class="form-control changeLang text-dark" id="language_dropdown">
            </select>
        </div>
    </div>
    
    <ul class="navbar-nav my-lg-0">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false"><img src="{{ asset('/images/users/user-new.png') }}"
                    alt="user" onerror="this.onerror=null; this.src='{{ asset('/images/users/user-new.png') }}';"
                    class="profile-pic"></a>
            <div class="dropdown-menu dropdown-menu-right scale-up">
                <ul class="dropdown-user">
                    <li>
                        <div class="dw-user-box">
                            <div class="u-img"><img src="{{ asset('/images/users/user-2.png') }}"
                                    onerror="this.onerror=null; this.src='{{ asset('/images/users/user-2.png') }}';"
                                    alt="user" style="max-width: 45px;"></div>
                            <div class="u-text">
                                <h4>{{ Auth::user()->name }}</h4>
                                <p class="text-muted">{{ session('user_role') }}</p>
                            </div>
                        </div>
                    </li>
                    <li role="separator" class="divider"></li>
                    <li><a href="{{ route('users.profile') }}"><i class="ti-user"></i>
                            {!! trans('lang.user_profile') !!}</a></li>
                    <li role="separator" class="divider"></li>
                    <li><a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                                class="fa fa-power-off"></i> {{ __('Logout') }}</a></li>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </ul>
            </div>
        </li>
    </ul>
    <div class="navbar-nav my-lg-0 multi-service-nav">
        @php
            $headerSections = \Illuminate\Support\Facades\DB::table('sections')
                ->where('isActive', 1)
                ->orderBy('name')
                ->get(['id', 'name', 'serviceType', 'serviceTypeFlag', 'sectionImage']);
            $cookieSectionId = request()->cookie('section_id', '');
            $cookieServiceType = request()->cookie('service_type', '');
            $activeHeaderSection = $headerSections->first(function ($section) use ($cookieSectionId, $cookieServiceType) {
                return $section->id === $cookieSectionId
                    && ($cookieServiceType === '' || $section->serviceTypeFlag === $cookieServiceType);
            });
            if (!$activeHeaderSection && $headerSections->isNotEmpty()) {
                $activeHeaderSection = $headerSections->first();
            }
            $activeSectionImage = $activeHeaderSection?->sectionImage ?: asset('images/kweek_icon.png');
            $activeSectionLabel = $activeHeaderSection?->name ?: trans('lang.select_section');
        @endphp
        <div class="nav-item dropdown" id="sectionPicker">
            <a class="nav-link dropdown-toggle waves-effect waves-dark" href="#" id="activeSectionLink"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img src="{{ $activeSectionImage }}" onerror="this.onerror=null; this.src='{{ asset('images/logo-light-icon.png') }}';" id="activeSectionLogo" style="height:40px; margin-right:5px;" alt="KWEEK">
                <span id="activeSectionName">{{ $activeSectionLabel }}</span>
            </a>
            <div class="dropdown-menu dropdown-service scale-up">
                <div class="dropdown-service_inner">
                    <div class="dropdown-service-top mb-4">
                        <h2>{{ trans('lang.modules_section') }}</h2>
                        <p>{{ trans('lang.select_module_monitor') }}</p>
                    </div>
                    <div id="sections_header">
                        @include('layouts.partials.sections-picker', [
                            'headerSections' => $headerSections,
                            'activeHeaderSection' => $activeHeaderSection,
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if($activeHeaderSection && ($cookieSectionId !== $activeHeaderSection->id || $cookieServiceType !== ($activeHeaderSection->serviceTypeFlag ?? '')))
        <script>
            setCookie('section_id', @json($activeHeaderSection->id), 1);
            setCookie('service_type', @json($activeHeaderSection->serviceTypeFlag ?? ''), 1);
        </script>
    @endif
</div>
