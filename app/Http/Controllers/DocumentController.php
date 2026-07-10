<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class DocumentController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "documents";
    }

    public function updateStatus(\Illuminate\Http\Request $request, $id)
    {
        $document = \App\Models\Document::findOrFail($id);
        $document->enable = $request->input('enable');
        $document->save();

        return response()->json(['success' => true]);
    }
}
