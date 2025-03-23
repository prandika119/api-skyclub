<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFieldImageRequest;
use App\Models\Field;
use App\Models\FieldImage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class FieldImageController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Field $field, StoreFieldImageRequest $request): Response
    {
        $data = $request->validated();
        $data['field_id'] = $field->id;

        if (isset($data['photo'])){
            $path = $data['photo']->store('fields', 'public');
            $data['photo'] = $path;
        }

        FieldImage::create($data);

        return response([
            'message' => 'Image created successfully'
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FieldImage $fieldImage): Response
    {
        Storage::disk('public')->delete($fieldImage->photo);
        $fieldImage->delete();
        return response([
            'message' => 'Image deleted successfully'
        ], 200);
    }
}
