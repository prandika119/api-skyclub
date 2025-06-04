<?php

namespace App\Http\Controllers;

use App\Http\Resources\FieldResource;
use App\Models\Field;
use App\Http\Requests\StoreFieldRequest;
use App\Http\Requests\UpdateFieldRequest;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Response;

class FieldController extends Controller
{


    /**
     * Get all fields
     *
     * Display a listing of the resource.
     */
    public function index(): Response{
        $fields = Field::all();
        return response([
            'message' => 'Success',
            'data' => $fields
        ]);
    }


    /**
     * Create new field
     *
     * Store a field
     */
    public function store(StoreFieldRequest $request): Response
    {
        $data = $request->validated();
        Field::create($data);
        return response([
            'message' => 'Field created successfully'
        ], 201);
    }

    /**
     * Get Field by id
     *
     * Display a field
     */
    public function show(Field $field): Response
    {
        return response([
            'message' => 'Success',
            'data' => new FieldResource($field)
        ]);

    }

    /**
     * Update Field
     *
     * Update a field
     */
    public function update(UpdateFieldRequest $request, Field $field): Response
    {
        $data = $request->validated();
        $field->update($data);
        return response([
            'message' => 'Field updated successfully'
        ], 200);
    }

    /**
     * Get Schedules
     *
     * Display a field's schedules
     * @param Field $field
     * @return Response
     */
    public function getSchedules(Field $field): Response
    {
        $startDate = request()->query('start_date');
        $endDate = request()->query('end_date');

        $schedules = Schedule::generateSchedule($field, $startDate, $endDate);
        return response([
            'message' => 'Success',
            'data' => $schedules
        ]);
    }
}
