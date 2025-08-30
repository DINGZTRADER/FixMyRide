<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RepairRequest;
use Illuminate\Support\Facades\Auth;

class RepairRequestController extends Controller
{
    // List all requests (user sees own, mechanic sees all)
    public function index()
    {
        $user = Auth::user();
        $requests = $user->role === 'mechanic'
            ? RepairRequest::all()
            : $user->repairRequests;

        return response()->json($requests);
    }

    // Create a new repair request
    public function store(Request $request)
    {
        $request->validate([
            'car_model' => 'required|string|max:255',
            'issue' => 'required|string',
        ]);

        $repairRequest = RepairRequest::create([
            'user_id' => Auth::id(),
            'car_model' => $request->car_model,
            'issue' => $request->issue,
            'status' => 'pending',
        ]);

        return response()->json($repairRequest, 201);
    }

    // Update request status (mechanic only)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,accepted,declined',
        ]);

        $repairRequest = RepairRequest::findOrFail($id);
        $repairRequest->status = $request->status;
        $repairRequest->save();

        return response()->json($repairRequest);
    }
}
