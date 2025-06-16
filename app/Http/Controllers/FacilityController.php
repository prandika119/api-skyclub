<?php

namespace App\Http\Controllers;

use App\Http\Resources\FacilityResource;
use App\Models\Facility;
use App\Http\Requests\StoreFacilityRequest;
use App\Http\Requests\UpdateFacilityRequest;
use App\Models\Field;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class FacilityController extends Controller
{
    /**
     * Get all facilities
     *
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $facilities = Facility::all();
        return response([
            'message' => 'Success',
            'data' => FacilityResource::collection($facilities)
        ]);
    }

    /**
     * Add Facility
     *
     * Add a facility to a field
     */
    public function addFacilityToField(Field $field, Facility $facility): Response
    {
        DB::table('fields_facilities')->insert([

            'field_id' => $field->id,
            'facility_id' => $facility->id
        ]);
        return response([
            'message' => 'Facility added to field successfully'
        ], 201);
    }

    /**
     * Delete Facility
     *
     * Remove a facility from a field
     */
    public function removeFacilityFromField(Field $field, Facility $facility): Response
    {
        DB::table('fields_facilities')->where('facility_id', $facility->id)->where('field_id', $field->id)->delete();
        return response([
            'message' => 'Facility removed from field successfully'
        ], 200);
    }
}
