<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\NearEarthObject;
use App\Services\NASA\NearEarthObjectsService;
use Illuminate\Http\Request;

class NeoAnalysisController extends Controller
{
    protected NearEarthObjectsService $nearEarthObjectsService;

    public function __construct(NearEarthObjectsService  $nearEarthObjectsService)
    {
        $this->nearEarthObjectsService = $nearEarthObjectsService;
    }
    public function getByDateRange(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $analyses = Analysis::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        return response()->json($analyses);
    }

    public function getCompleteList()
    {
        $analyses = Analysis::orderBy('date')->get();
        return response()->json($analyses);
    }

    public function getDetails($id)
    {
        $neo = NearEarthObject::with('analyses')->findOrFail($id);
        return response()->json($neo);
    }

    public function sendQuery(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end = \Carbon\Carbon::parse($validated['end_date']);
        if ($start->diffInDays($end) > 7) {
            return response()->json(['error' => 'Date range must not be greater than 7 days'], 400);
        }

        $this->nearEarthObjectsService->fetch($validated['start_date'], $validated['end_date']);
        $this->nearEarthObjectsService->analyse($validated['start_date'], $validated['end_date']);

        $request->merge($validated);

        return $this->getByDateRange($request);
    }
}

