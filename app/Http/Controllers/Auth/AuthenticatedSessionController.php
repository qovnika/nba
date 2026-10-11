<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Http\Middleware;

class AuthenticatedSessionController extends Controller
{

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response
    {
        $request->authenticate();

        $request->session()->regenerate();

        return response()->noContent();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function storeToken(LoginRequest $request)
    {
        $user = $request->validate([
            "email" => "required|email",
            "password" => "required"
        ]);

        //Try to authenticate user
        if (!auth()->attempt($user)) {
            $this->destroy($request);
            return Controller::responder(false, "Could not log you in as the Email or password you provided do not match our records.", ["errors" => "The credentials you provided could not be verified."]);
        } else {
            //If the status of the user is not active, do not log the user in
            if (!$request->user()->email_verified_at) {
                return Controller::responder(false, "Your email address has not been verified. Please go to the email address you provided and enter the code that was sent.", ["errors" => "Your account has been deactivated. Please contact the system administrator to resolve this."]);
            }
            session()->regenerate();    // Avoid session fixation
            $token = $request->user()->createToken("api_token");

            return Controller::responder(true, "You have logged in successfully.", [
                'token' => $token->plainTextToken,
                "success" => "You have logged in successfully."
            ]);
        }

    }

    public function google(Request $request)
    {
        $googleUser = Socialite::driver('google')->userFromToken($request->access_token);
        $fname = explode(" ", $googleUser->name)[0];
        $lname = explode(" ", $googleUser->name)[1];

        $user = User::updateOrCreate([
            'email' => $googleUser->email,
        ], [
            'fname' => $fname,
            'lname' => $lname,
            'status' => 1,
            'provider' => true
        ]);
     
        Auth::login($user);
        $token = $user->createToken("api_key");
     
        return Controller::responder(true, "You have logged in successfully using your google account.", $token->plainTextToken);    
    }

    public function getTwitterToken (Request $request) {
        $twitter_client_id = "WHJBdWkza293Q0pqX1lXdmFwaS06MTpjaQ";
        $twitter_client_secret = "kkaaUuCRAUvAz1l9MDpkUZLYqlGAwypiiEm9Mx21NUVcX0K7qN";
        $data = [
                "grant_type" => "authorization_code",
                "code" => $request->code,
                "client_id" => $twitter_client_id,
                "redirect_uri" => "https://securable.com.ng/tailor-api/public/api/getTwitterToken",
                "code_verifier" => "y_SfRG4BmOES02uqWeIkIgLQAlTBggyf_G7uKT51ku8",
            ];
        $response = Http::withHeaders([
            "Content-Type" => "application/x-www-form-urlencoded",
            "Authorization" => "Basic ".base64_encode($twitter_client_id.":".$twitter_client_secret),
            "Accept" => "*/*"
        ])->asForm()->post('https://api.twitter.com/2/oauth2/token', $data);
    
        if($response->successful()) {
            $token = $response->json("access_token");
            return Redirect::to("https://mytailor.vercel.app?token=".$token);
        } else {
            return $response->json();
        }
    }

    public function twitterRedirect ($token) {
        $response = Http::withHeaders([
            "Content-Type" => "application/json",
            "Authorization" => "Bearer ".$token,
            "Accept" => "*/*"
        ])->asForm()->get('https://api.twitter.com/2/users/me');
    
        if($response->successful()) {
            $user = $response->json();
            return $user;
        } else {
            return $response->json();
        }
    }

    public function getTwitterUser ($token, $uid) {
        $response = Http::withHeaders([
            "Content-Type" => "application/json",
            "Authorization" => "Bearer ".$token,
            "Accept" => "*/*"
        ])->asForm()->get('https://api.twitter.com/2/users/'.$uid);
    
        if($response->successful()) {
            $user = $response->json();
            return $user;
        } else {
            return $response->json();
        }
    }

    public function twitter (Request $request) {
//        $twitterUser = Socialite::driver('twitter')->userFromTokenAndSecret($request->access_token, "kkaaUuCRAUvAz1l9MDpkUZLYqlGAwypiiEm9Mx21NUVcX0K7qN");
        $twitterUser = $this->twitterRedirect($request->access_token);
        $fname = explode(" ", $twitterUser->name)[0];
        $lname = explode(" ", $twitterUser->name)[1];

        $user = User::updateOrCreate([
            'email' => $twitterUser->username."@twitter.com",
        ], [
            'fname' => $fname,
            'lname' => $lname,
            'status' => 1,
            'provider' => true
        ]);
     
        Auth::login($user);
        $token = $user->createToken("api_key");
     
        return Controller::responder(true, "You have logged in successfully using your google account.", $token->plainTextToken);    
    }
}