<?php

namespace App\Http\Controllers;


class VehicleController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function carMake()
    {
        return view('carMake.index');
    }

    public function carMakeEdit($id)
    {
        $carMake = \Illuminate\Support\Facades\DB::table('car_makes')->where('id', $id)->first();
        return view('carMake.edit')->with('id', $id)->with('carMake', $carMake);
    }

    public function carMakeCreate()
    {
        return view('carMake.create');
    }

    public function carModel()
    {
        return view('carModel.index');
    }

    public function carModelEdit($id)
    {
        $carModel = \Illuminate\Support\Facades\DB::table('car_models')->where('id', $id)->first();
        return view('carModel.edit')->with('id', $id)->with('carModel', $carModel);
    }

    public function carModelCreate()
    {
        return view('carModel.create');
    }

    public function vehicleType()
    {
        return view('vehicleType.index');
    }

    public function vehicleTypeEdit($id)
    {
        return view('vehicleType.edit')->with('id', $id);
    }

    public function vehicleTypeCreate()
    {
        return view('vehicleType.create');
    }

    public function carMakeDatatable(\Illuminate\Http\Request $request)
    {
        $draw = intval($request->input('draw', 1));
        $start = intval($request->input('start', 0));
        $length = intval($request->input('length', 10));
        $search = $request->input('search.value', '');

        $query = \Illuminate\Support\Facades\DB::table('car_makes');

        if (!empty($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        $totalRecords = \Illuminate\Support\Facades\DB::table('car_makes')->count();
        $filteredRecords = $query->count();

        $records = $query->skip($start)->take($length)->get();
        
        $data = [];
        foreach ($records as $record) {
            $row = [];
            
            $userPermissions = json_decode(session('user_permissions', '[]'), true) ?: [];
            $checkDeletePermission = in_array('make.delete', $userPermissions);

            $editUrl = route('carMake.edit', $record->id);
            $actions = '<span class="action-btn">';
            $actions .= '<a href="' . $editUrl . '"><i class="mdi mdi-lead-pencil"></i></a>';
            if ($checkDeletePermission) {
                $actions .= '<a id="' . $record->id . '" class="delete-btn" name="car-make-delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i></a>';
            }
            $actions .= '</span>';

            $row[] = e($record->name);
            
            // Toggle active
            $activeChecked = $record->isActive ?? false ? 'checked' : '';
            $row[] = '<label class="switch"><input type="checkbox" ' . $activeChecked . ' id="' . $record->id . '" name="isActive"><span class="slider round"></span></label>';

            $row[] = $actions;
            $data[] = $row;
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function storeCarMake(\Illuminate\Http\Request $request)
    {
        try {
            $id = $request->input('id');
            $data = [
                'name' => $request->input('name'),
                'isActive' => filter_var($request->input('isActive'), FILTER_VALIDATE_BOOLEAN)
            ];

            if ($id) {
                \Illuminate\Support\Facades\DB::table('car_makes')->where('id', $id)->update($data);
            } else {
                $data['id'] = (string) \Illuminate\Support\Str::uuid();
                \Illuminate\Support\Facades\DB::table('car_makes')->insert($data);
                $id = $data['id'];
            }
            return response()->json(['success' => true, 'id' => $id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateCarMake(\Illuminate\Http\Request $request, $id)
    {
        return $this->storeCarMake($request);
    }

    public function destroyCarMake(\Illuminate\Http\Request $request)
    {
        $id = $request->input('id');
        if ($id) {
            \Illuminate\Support\Facades\DB::table('car_makes')->where('id', $id)->delete();
        }
        return response()->json(['success' => true]);
    }

    public function carModelDatatable(\Illuminate\Http\Request $request)
    {
        $draw = intval($request->input('draw', 1));
        $start = intval($request->input('start', 0));
        $length = intval($request->input('length', 10));
        $search = $request->input('search.value', '');

        $query = \Illuminate\Support\Facades\DB::table('car_models')
                   ->join('car_makes', 'car_models.car_make_id', '=', 'car_makes.id')
                   ->select('car_models.*', 'car_makes.name as make_name');

        if (!empty($search)) {
            $query->where('car_models.name', 'like', "%{$search}%")
                  ->orWhere('car_makes.name', 'like', "%{$search}%");
        }

        $totalRecords = \Illuminate\Support\Facades\DB::table('car_models')->count();
        $filteredRecords = $query->count();

        $records = $query->skip($start)->take($length)->get();
        
        $data = [];
        foreach ($records as $record) {
            $row = [];
            
            $userPermissions = json_decode(session('user_permissions', '[]'), true) ?: [];
            $checkDeletePermission = in_array('model.delete', $userPermissions);

            $editUrl = route('carModel.edit', $record->id);
            $actions = '<span class="action-btn">';
            $actions .= '<a href="' . $editUrl . '"><i class="mdi mdi-lead-pencil"></i></a>';
            if ($checkDeletePermission) {
                $actions .= '<a id="' . $record->id . '" class="delete-btn" name="car-model-delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i></a>';
            }
            $actions .= '</span>';

            $row[] = e($record->make_name);
            $row[] = e($record->name);
            
            // Toggle active
            $activeChecked = $record->isActive ?? false ? 'checked' : '';
            $row[] = '<label class="switch"><input type="checkbox" ' . $activeChecked . ' id="' . $record->id . '" name="isActive"><span class="slider round"></span></label>';

            $row[] = $actions;
            $data[] = $row;
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function storeCarModel(\Illuminate\Http\Request $request)
    {
        try {
            $id = $request->input('id');
            $data = [
                'name' => $request->input('name'),
                'car_make_id' => $request->input('car_make_id'),
                'isActive' => filter_var($request->input('isActive'), FILTER_VALIDATE_BOOLEAN)
            ];

            if ($id) {
                \Illuminate\Support\Facades\DB::table('car_models')->where('id', $id)->update($data);
            } else {
                $data['id'] = (string) \Illuminate\Support\Str::uuid();
                \Illuminate\Support\Facades\DB::table('car_models')->insert($data);
                $id = $data['id'];
            }
            return response()->json(['success' => true, 'id' => $id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateCarModel(\Illuminate\Http\Request $request, $id)
    {
        return $this->storeCarModel($request);
    }

    public function destroyCarModel(\Illuminate\Http\Request $request)
    {
        $id = $request->input('id');
        if ($id) {
            \Illuminate\Support\Facades\DB::table('car_models')->where('id', $id)->delete();
        }
        return response()->json(['success' => true]);
    }
}
