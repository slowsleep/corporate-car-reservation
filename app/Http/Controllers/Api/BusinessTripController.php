<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusinessTrip;

class BusinessTripController extends Controller
{
    public function index()
    {
        $allTrips = BusinessTrip::all();
        return response()->json($allTrips, 200);
    }
}
