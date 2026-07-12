<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class SettingsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function social()
    {
        return view("settings.app.social");
    }

    public function globals()
    {
        return view("settings.app.global");
    }

    public function getSectionsList(): JsonResponse
    {
        $flags = ['delivery-service', 'ondemand-service', 'ecommerce-service'];
        $sections = \DB::table('sections')
            ->whereIn('serviceTypeFlag', $flags)
            ->orderBy('name')
            ->get(['id', 'name', 'serviceType', 'serviceTypeFlag']);
        return response()->json($sections);
    }

    public function getVendorsBySection(Request $request): JsonResponse
    {
        $sectionId   = $request->query('section_id');
        $serviceType = $request->query('service_type', '');
        $mode        = $request->query('mode', 'section'); // 'section' or 'all'

        if (str_contains($serviceType, 'On Demand')) {
            $query = \DB::table('app_users')->where('role', 'provider')->orderBy('firstName');
            if ($mode === 'section' && $sectionId) {
                $query->where('section_id', $sectionId);
            }
            $users = $query->get(['id', 'firstName', 'lastName']);
            $data  = $users->map(fn($u) => ['id' => $u->id, 'label' => trim($u->firstName . ' ' . $u->lastName)]);
        } else {
            $query = \DB::table('vendors')->orderBy('title');
            if ($mode === 'section' && $sectionId) {
                $query->where('section_id', $sectionId);
            }
            $vendors = $query->get(['id', 'title']);
            $data    = $vendors->map(fn($v) => ['id' => $v->id, 'label' => $v->title]);
        }
        return response()->json($data);
    }

    public function cod()
    {
        return view('settings.app.cod');
    }

    public function stripe()
    {
        return view('settings.app.stripe');
    }

    public function mobileGlobals()
    {
        return view('settings.mobile.globals');
    }

    public function razorpay()
    {
        return view('settings.app.razorpay');
    }

    public function paypal()
    {
        return view('settings.app.paypal');
    }

    public function radiosConfiguration()
    {
        return view("settings.app.radiosConfiguration");
    }

    public function wallet()
    {
        return view('settings.app.wallet');
    }


    public function payfast()
    {
        return view('settings.app.payfast');
    }

    public function paystack()
    {
        return view('settings.app.paystack');
    }

    public function mercadopago()
    {
        return view('settings.app.mercadopago');
    }

    public function flutterwave()
    {
        return view('settings.app.flutterwave');
    }

    public function xendit()
    {
        return view('settings.app.xendit');
    }

    public function midTrans()
    {
        return view('settings.app.midTrans');
    }

    public function orangePay()
    {
        return view('settings.app.orangePay');
    }

    public function arroPayMaya()
    {
        return view('settings.app.arroPayMaya');
    }

    public function arroPayMayaQr()
    {
        return view('settings.app.arroPayMayaQr');
    }

    public function arroPayInstapay()
    {
        return view('settings.app.arroPayInstapay');
    }

    public function arroPayAuth()
    {
        return view('settings.app.arroPayAuth');
    }

    public function deliveryCharge()
    {
        return view('settings.app.deliveryCharge');
    }

    public function languages()
    {
        return view('settings.languages.index');
    }

    public function languagesedit($id)
    {
        return view('settings.languages.edit')->with('id', $id);
    }

    public function languagescreate()
    {
        return view('settings.languages.create');
    }

    public function carMake()
    {
        return view('settings.carMake.index');
    }

    public function carMakeEdit($id)
    {
        return view('settings.carMake.edit')->with('id', $id);
    }

    public function carMakeCreate()
    {
        return view('settings.carMake.create');
    }

    public function carModel()
    {
        return view('settings.carModel.index');
    }

    public function carModelEdit($id)
    {
        return view('settings.carModel.edit')->with('id', $id);
    }

    public function carModelCreate()
    {
        return view('settings.carModel.create');
    }

    public function vehicleType()
    {
        return view('settings.vehicleType.index');
    }


    public function vehicleTypeEdit($id)
    {
        return view('settings.vehicleType.edit')->with('id', $id);
    }

    public function vehicleTypeCreate()
    {
        return view('settings.vehicleType.create');
    }

    public function rentalvehicleType()
    {
        return view('rentalvehicleType.index');
    }

    public function rentalvehicleTypeEdit($id)
    {
        return view('rentalvehicleType.edit')->with('id', $id);
    }

    public function rentalvehicleTypeCreate()
    {
        return view('rentalvehicleType.create');
    }

    public function promos()
    {
        return view('settings.promos.index');
    }

    public function promosEdit($id)
    {
        return view('settings.promos.edit')->with('id', $id);
    }

    public function promosCreate()
    {
        return view('settings.promos.create');
    }

    public function complaints()
    {
        return view('complaints.index');
    }

    public function complaintsEdit($id)
    {
        return view('complaints.edit')->with('id', $id);

    }

    public function sos()
    {
        return view('sos.index');
    }

    public function sosEdit($id)
    {
        return view('sos.edit')->with('id', $id);

    }

    public function specialOffer()
    {
        return view('settings.app.specialDiscountOffer');
    }

    public function menuItems()
    {
        return view('settings.menu_items.index');
    }

    public function menuItems2()
    {
        return view('settings.menu_items.index_newbackup');
    }

    public function menuItemsCreate()
    {
        return view('settings.menu_items.create');
    }

    public function menuItemsEdit($id)
    {
        return view('settings.menu_items.edit')->with('id', $id);
    }

    public function rentalDiscount()
    {
        return view('rentalDiscount.index');
    }

    public function rentalDiscountEdit($id)
    {
        return view('rentalDiscount.edit')->with('id', $id);
    }

    public function rentalDiscountCreate()
    {
        return view('rentalDiscount.create');
    }

    public function homepageTemplate()
    {
        return view('homepage_Template.index');
    }

    public function rentalvehicle()
    {
        return view('rentalVehicle.index');
    }

    public function rentalVehicleView($id)
    {
        return view('rentalVehicle.view')->with('id', $id);
    }

    public function footerTemplate()
    {
        return view('footerTemplate.index');
    }

    public function emailTemplatesIndex()
    {
        return view('email_templates.index');
    }

    public function emailTemplatesSave($id = '')
    {
        return view('email_templates.save', ['id' => $id]);
    }

    public function banners()
    {
        return view("settings.app.banners");
    }
    public function businessModel()
    {
        return view('settings.app.businessModel');
    }
    public function documentVerification()
    {
        return view('settings.app.documentVerificationSetting');
    }
    public function scheduleOrderNotification()
    {
        return view('settings.app.schedule_notification');
    }
    public function maintenanceSettings()
    {
        return view('settings.app.maintenance_settings');
    }
}
