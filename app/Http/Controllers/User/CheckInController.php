<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\CoinService;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function index(Request $request, CoinService $coins)
    {
        return view('user.check-in', ['status' => $coins->status($request->user())]);
    }
    public function store(Request $request, CoinService $coins)
    {
        return response()->json($coins->checkIn($request->user()->id));
    }
}
