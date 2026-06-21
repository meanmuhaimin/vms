<?php

namespace App\Http\Controllers;

use App\Models\VisitorLog;
use App\Models\WayfindingLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WayfindingController extends Controller
{
    public function show(Request $request, string $logId): JsonResponse
    {
        $visitorLog = VisitorLog::query()
            ->where('log_id', $logId)
            ->where('status', 'APPROVED')
            ->firstOrFail();

        $location = WayfindingLocation::query()
            ->where('location_id', $visitorLog->assigned_location_id)
            ->firstOrFail();

        return response()->json([
            'log_id' => $visitorLog->log_id,
            'assigned_location_id' => $location->location_id,
            'display_name' => $location->display_name,
            'building_sector' => $location->building_sector,
            'floor_label' => $location->floor_label,
            'map_asset_path' => $location->map_asset_path,
            'route_steps' => $location->route_steps,
        ]);
    }

    public function publicDirectory(Request $request): JsonResponse
    {
        $locations = WayfindingLocation::query()
            ->where('is_public', true)
            ->orderBy('display_name')
            ->get(['location_id', 'display_name', 'building_sector', 'floor_label']);

        return response()->json([
            'locations' => $locations,
        ]);
    }
}
