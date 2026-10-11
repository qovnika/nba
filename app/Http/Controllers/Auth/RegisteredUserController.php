<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserRegistered;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'fname' => ['required', 'string', 'max:255'],
            'lname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:255', 'unique:users,phone'],
            'scn' => ['required', 'string', 'max:255', 'unique:users,scn'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'fname' => $request->fname,
            'onames' => $request->onames,
            'lname' => $request->lname,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'dob' => $request->dob,
            'profilePic' => $request->profilePic,
            'gender' => $request->gender,
            'scn' => $request->scn,
            'year_of_call' => $request->year_of_call,
            'usertype_id' => 1,
            'password' => Hash::make($request->password),
            "email_code" => rand(10000, 99999),
            "title_id" => $request->title ? $request->title : 1
        ]);

        event(new Registered($user));
        Mail::to($user->email)->send(new UserRegistered($user));

        return Controller::responder(true, "Your account has been created successfully. An email has been sent to the email address you provided. Please follow the instructions in that email. Be sure to check your spam folder if you do not see the email in your inbox.", $user);
    }

    public function getUser (Request $request): Response
    {
        $request->validate([
            'email' => ['required', 'string', 'email']
        ]);

        $user = User::where(["email" => $request->email])->first();

        return response([
            "fullName" => $user->name,
            "email" => $user->email
        ]);
    }

    public function currentUser (Request $request) {
        return Auth::user();
    }

}
