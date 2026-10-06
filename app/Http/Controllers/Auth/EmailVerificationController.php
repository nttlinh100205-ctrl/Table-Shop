<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, string $id, string $hash)
    {
        // The expiring signed link proves email ownership, independently of device login.
        [$user, $changed] = DB::transaction(function () use ($id, $hash) {
            $user = User::whereKey($id)->lockForUpdate()->firstOrFail();
            abort_unless(hash_equals(sha1($user->getEmailForVerification()), $hash), 403);
            $changed = !$user->hasVerifiedEmail();
            if ($changed) $user->markEmailAsVerified();
            return [$user, $changed];
        });
        if ($changed) event(new Verified($user));
        // Never log the phone in or replace another account's session using an email link.
        if ((string)$request->user()?->id === (string)$user->id) {
            return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'user.home')
                ->with('success', 'Xác thực email thành công!');
        }
        return response()->view('auth.email-verified')->header('Cache-Control', 'no-store, private');
    }

    public function status(Request $request)
    {
        $user = $request->user()->fresh();
        abort_unless($user, 401);
        return response()->json([
            'user_id'=>$user->id,
            'verified'=>$user->hasVerifiedEmail(),
            'redirect'=>$user->hasVerifiedEmail() ? route($user->isAdmin() ? 'admin.dashboard' : 'user.home') : null,
        ])->header('Cache-Control', 'no-store, private');
    }
}
