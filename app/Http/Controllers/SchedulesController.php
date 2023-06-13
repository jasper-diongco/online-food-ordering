<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class SchedulesController extends Controller
{
    public function index(Request $request) {
        $store_id = $request->store_id ?? '';

        $schedules = Schedule::where('store_id', $store_id)->get();

        return [
            'schedules' => $schedules
        ];
    }

    public function show($schedule_id) {
        $schedule = Schedule::findOrFail($schedule_id);

        return [
            'schedule' => $schedule
        ];
    }

    public function store(Request $request) {
        $request->validate([
            'store_id' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'location_description' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'days' => 'required',
        ]);

        $schedule = Schedule::create($request->all());

        return [
            'schedule' => $schedule
        ];
    }

    public function update(Request $request, $schedule_id) {
        $request->validate([
            'store_id' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'location_description' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'days' => 'required',
        ]);

        $schedule = Schedule::findOrFail($schedule_id);

        $schedule->update($request->all());

        return [
            'schedule' => $schedule
        ];
    }

    public function destroy($schedule_id) {

        $schedule = Schedule::findOrFail($schedule_id);

        $schedule->delete();

        return [
            'message' => 'Schedule Deleted'
        ];
    }
}
