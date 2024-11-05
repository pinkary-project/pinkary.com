<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MetaData;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Support\Str;

final readonly class OpenGraphController
{
    /**
     * Display the OpenGraph Validator and Preview page.
     */
    public function index(): View
    {
        return view('opengraph.validator');
    }

    /**
     * Validate the input and return OpenGraph metadata.
     */
    public function validate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:url',
            'input' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $input = $request->input('input');

        $metadata = (new MetaData($input))->fetch();

        if ($metadata->isEmpty()) {
            return response()->json(['errors' => ['input' => ['No valid OpenGraph data found.']]], 422);
        }

        return response()->json(['metadata' => $metadata]);
    }
}
