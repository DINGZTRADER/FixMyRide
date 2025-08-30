<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RepairRequest;
use Illuminate\Support\Facades\Auth;

class RepairRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = RepairRequest::with(['user:id,name,email', 'mechanic:id,name,email'])->orderByDesc('created_at');

        if ($user && $user->role === 'owner') {
            $query->where('user_id', $user->id);
        } elseif ($user && $user->role === 'mechanic' && $request->query('filter') === 'mine') {
            $query->where('assigned_to', $user->id);
        }

        $perPage = (int) $request->query('per_page', 10);
        $paginated = $query->paginate($perPage);

        $paginated->getCollection()->transform(function ($r) {
            return [
                'id' => $r->id,
                'car_make' => $r->car_make,
                'car_model' => $r->car_model,
                'year' => $r->year,
                'problem_type' => $r->problem_type,
                'phone_number' => $r->phone_number,
                'willing_to_pay' => (bool) $r->willing_to_pay,
                'issue' => $r->issue,
                'status' => $r->status,
                'owner_name' => optional($r->user)->name,
                'owner_email' => optional($r->user)->email,
                'assigned_to_name' => optional($r->mechanic)->name,
                'assigned_to_email' => optional($r->mechanic)->email,
                'created_at' => optional($r->created_at)->toIso8601String(),
            ];
        });

        return response()->json($paginated);
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'owner') {
            return response()->json(['message' => 'Only owners can create requests'], 403);
        }
        $request->validate([
            'car_make' => 'required|string|max:255',
            'car_model' => 'required|string|max:255',
            'year' => 'nullable|integer|min:1900|max:2100',
            'problem_type' => 'required|string|in:Engine,Electrical,Brakes,Transmission,Suspension,General Service,Other',
            'phone_number' => 'required|string|max:32',
            'willing_to_pay' => 'sometimes|boolean',
            'issue' => 'nullable|string',
        ]);

        $repairRequest = RepairRequest::create([
            'user_id' => Auth::id(),
            'car_make' => $request->car_make,
            'car_model' => $request->car_model,
            'year' => $request->year,
            'problem_type' => $request->problem_type,
            'phone_number' => $request->phone_number,
            'willing_to_pay' => (bool) $request->willing_to_pay,
            'issue' => $request->issue,
            'status' => 'pending',
        ]);

        return response()->json($repairRequest, 201);
    }

    public function updateStatus(Request $request, $id)
    {
        if (Auth::user()->role !== 'mechanic') {
            return response()->json(['message' => 'Only mechanics can update status'], 403);
        }
        $request->validate([
            'status' => 'required|in:pending,accepted,declined',
        ]);

        $repairRequest = RepairRequest::findOrFail($id);
        $repairRequest->status = $request->status;
        $repairRequest->save();

        return response()->json($repairRequest);
    }

    public function assign($id)
    {
        $user = Auth::user();
        if ($user->role !== 'mechanic') {
            return response()->json(['message' => 'Only mechanics can assign requests'], 403);
        }
        $repairRequest = RepairRequest::findOrFail($id);
        $repairRequest->assigned_to = $user->id;
        $repairRequest->save();
        return response()->json($repairRequest);
    }

    public function unassign($id)
    {
        $user = Auth::user();
        if ($user->role !== 'mechanic') {
            return response()->json(['message' => 'Only mechanics can unassign requests'], 403);
        }
        $repairRequest = RepairRequest::findOrFail($id);
        $repairRequest->assigned_to = null;
        $repairRequest->save();
        return response()->json($repairRequest);
    }
}

