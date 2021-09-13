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

        $daylogs = Daylog::where('log_date', '>=', $from->format('Y-m-d'))
            ->where('log_date', '<=', $to->format('Y-m-d'))
            ->get();

        return response()->json(['daylogs' => DaylogResource::collection($daylogs)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
