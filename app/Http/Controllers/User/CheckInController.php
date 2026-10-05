<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\CoinService;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function index(Request $request, CoinService $coins)
    {
        return view('user.check-in', ['status' => $coins->status($request->user()),
            'transactions' => \Illuminate\Support\Facades\DB::table('coin_transactions')
                ->where('user_id', $request->user()->id)->latest('id')->limit(20)->get()]);
    }
    public function store(Request $request, CoinService $coins)
    {
        return response()->json($coins->checkIn($request->user()->id));
    }
}
