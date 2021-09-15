<?php

namespace App\Http\Controllers;

use App\Http\Resources\DaylogResource;
use App\Models\Daylog;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DaylogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make([
            'from' => $request->query('from'),
            'to'   => $request->query('to'),
        ], ['from' => ['required', 'date_format:Y-m-d'],
            'to'   => ['required', 'date_format:Y-m-d'],
        ],);

        $data = $validator->validated();

        $from = new CarbonImmutable($data['from'] . ' 00:00:00');
        $to   = new CarbonImmutable($data['to'] . ' 23:59:59');

        // validation: period cannot be longer than 31 days
        if ($to->diffInDays($from) < 0 || $to->diffInDays($from) > 31) {
            abort(400, 'Period not within 31 days.');
        }

        $daylogs = Daylog::where('log_date', '>=', $from)
            ->where('log_date', '<=', $to)
            ->get();

        return response()->json(['daylogs' => DaylogResource::collection($daylogs)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'log_date'               => ['required', 'date'],
            'has_alcohol'            => ['sometimes', 'nullable', 'boolean'],
            'has_alcohol_in_evening' => ['sometimes', 'nullable', 'boolean'],
            'has_smoked'             => ['sometimes', 'nullable', 'boolean'],
            'wake_at'                => ['sometimes', 'nullable', 'date_format:H:i'],
            'first_meal_at'          => ['sometimes', 'nullable', 'date_format:H:i'],
            'last_meal_at'           => ['sometimes', 'nullable', 'date_format:H:i'],
            'sleep_at'               => ['sometimes', 'nullable', 'date_format:H:i'],
        ]);

        $daylog = new Daylog();

        $daylog->log_date = new CarbonImmutable($data['log_date']);
        unset($data['log_date']);

        // check whether record for this date already exists
        if (Daylog::where('log_date', $daylog->log_date)->count() > 0) {
            abort(409, 'Daylog with this date already exists.');
        }

        $daylog->fillAnswers($data)
            ->save();

        return response()->json(['daylog' => new DaylogResource($daylog)]);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $date)
    {
        $validator = Validator::make(['date' => $date], [
            'date' => 'required|date',
        ]);

        $data = $validator->validated();

        $date   = new CarbonImmutable($data['date']);
        $daylog = Daylog::where('log_date', $date)->firstOrFail();

        return response()->json(['daylog' => new DaylogResource($daylog)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'has_alcohol'            => ['sometimes', 'nullable', 'string'],
            'has_alcohol_in_evening' => ['sometimes', 'nullable', 'string'],
            'has_smoked'             => ['sometimes', 'nullable', 'string'],
            'wake_at'                => ['sometimes', 'nullable', 'date_format:H:i'],
            'first_meal_at'          => ['sometimes', 'nullable', 'date_format:H:i'],
            'last_meal_at'           => ['sometimes', 'nullable', 'date_format:H:i'],
            'sleep_at'               => ['sometimes', 'nullable', 'date_format:H:i'],
        ]);

        $daylog = Daylog::findOrFail($id);

        $daylog->fillAnswers($data)
            ->save();

        return response()->json(['daylog' => new DaylogResource($daylog)]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
