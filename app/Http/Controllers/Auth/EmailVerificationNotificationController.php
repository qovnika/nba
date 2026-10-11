<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Mail\UserVerified;
use Illuminate\Support\Facades\Mail;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['status' => 'verification-link-sent']);
    }

    public function verifyEmail (Request $request) {
        $user = User::find($request->id);
        if ($user->email_code == $request->code) {
            Mail::to($user->email)->send(new UserVerified($user));
            $user->email_verified_at = now();
            $user->save();
    
            return Controller::responder(true, "Successfully verified your email address.", $user);
        } else {
            return Controller::responder(false, "We were not able to verify your email address as the code you entered is not correct.", $user);
        }
    }
}
