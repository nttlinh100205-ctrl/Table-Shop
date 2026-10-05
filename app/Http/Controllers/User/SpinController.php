<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Prize;
use App\Services\SpinService;
use Illuminate\Http\Request;

class SpinController extends Controller
{
    public function index(Request $request)
    {
        return view('user.spin', ['prizes' => Prize::where('is_active', true)->orderBy('id')->get(),
            'histories' => $request->user()->spinHistories()->limit(20)->get()]);
    }
    public function store(Request $request, SpinService $service)
    {
        $data = $request->validate(['request_id' => 'required|uuid']);
        $history = $service->spin($request->user()->id, $data['request_id']);
        return response()->json(['result' => $history, 'tickets' => $request->user()->fresh()->spin_tickets]);
    }
}
