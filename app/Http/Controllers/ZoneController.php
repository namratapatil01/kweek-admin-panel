<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ZoneController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function moduleSlug(): string
    {
        return 'zones';
    }

    public function index(): View
    {
        return view('zone.index');
    }

    public function create(): View
    {
        return view('zone.create');
    }

    public function edit(...$params): View
    {
        return view('zone.edit', [
            'id' => (string) end($params),
        ]);
    }

    public function locationSearch(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        if (strlen($query) < 3) {
            return response()->json([]);
        }

        $response = Http::withHeaders([
            'User-Agent' => config('app.name', 'KWEEK') . ' Admin Panel',
            'Accept' => 'application/json',
        ])->get('https://nominatim.openstreetmap.org/search', [
            'q' => $query,
            'format' => 'json',
            'addressdetails' => 1,
            'limit' => 6,
        ]);

        return response()->json($response->json() ?: []);
    }

    public function locationReverse(Request $request): JsonResponse
    {
        $lat = $request->query('lat');
        $lon = $request->query('lon');

        if ($lat === null || $lon === null) {
            return response()->json([]);
        }

        $response = Http::withHeaders([
            'User-Agent' => config('app.name', 'KWEEK') . ' Admin Panel',
            'Accept' => 'application/json',
        ])->get('https://nominatim.openstreetmap.org/reverse', [
            'lat' => $lat,
            'lon' => $lon,
            'format' => 'json',
            'addressdetails' => 1,
        ]);

        return response()->json($response->json() ?: []);
    }
}
