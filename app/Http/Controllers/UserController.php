<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserRegistered;
use App\Mail\PasswordForgotten;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->id) {
            return User::find($request->id);
        } elseif ($request->term && $request->term !== "") {
        	$users = User::where("fname", "LIKE", "%".$request->term."%")->orWhere("lname", "LIKE", "%".$request->term."%")->orWhere("scn", "LIKE", "%".$request->term."%")->orWhere("onames", "LIKE", "%".$request->term."%")->get();
        	return Controller::responder(true, "Successfully retrieved users using search term: ".$request->term.".",  $users);
        } elseif ($request->all) {
        	$users = User::all();
        	return Controller::responder(true, "Successfully retrieved users.", $users);
        } elseif ($request->page) {
        	$users = User::orderBy("id", "DESC")->paginate(10);
        	return Controller::responder(true, "Successfully retrieved users.", $users);
        } else {
        	return Controller::responder(false, "No user with the details you provided exist on the database.", []);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendEmailVerificationMail()
    {
        $users = User::all();
        foreach ($users as $user) {
            if (!$user->email_verified_at) {
                Mail::to($user->email)->send(new UserRegistered($user));
            }
        }
    }

    /**
     * Resend email verification Mail.
     *
     * @return \Illuminate\Http\Response
     */
    public function resendEmailVerificationMail(Request $request)
    {
        $user = User::find($request->id);

        if (!$user) {
            return Controller::responder(false, "User not found.", []);
        }

        if (!$user->email_verified_at) {
            Mail::to($user->email)->send(new UserRegistered($user));
            return Controller::responder(true, "Successfully sent email verification mail to ".$user->email.".", $user);
        } else {
            return Controller::responder(false, "The user you are trying to resend email verification mail to is already verified.", $user);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUserRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUserRequest  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'fname' => ['required', 'string', 'max:255'],
            'lname' => ['required', 'string', 'max:255'],
        ]);

        $user = User::find($request->id);
        $user->fname = $request->fname;
        $user->onames = $request->onames;
        $user->lname = $request->lname;
        if ($request->email) $user->email = $request->email;
        $user->phone = $request->phone;
        if ($request->status) $user->status = $request->status;
        if ($request->scn) $user->scn = $request->scn;
        if ($request->dob) $user->dob = $request->dob;
        if ($request->title_id) $user->title_id = $request->title_id;
        if ($request->gender) $user->gender = $request->gender;
        if ($request->year_of_call) $user->year_of_call = $request->year_of_call;
        if ($request->profilePic) $user->profilePic = $request->profilePic;
        if ($request->usertype_id) $user->usertype_id = $request->usertype_id;
        if ($request->deactivate) $user->tokens()->delete();

        $user->save();

        return Controller::responder(true, "The user has been updated successfully.", $user);
    }

	public function updatePassword (Request $request) {
		$request->validate([
		    'id' => ['required'],
		    'password' => ['required', 'confirmed', Rules\Password::defaults()],
		]);

		$user = User::find($request->id);
		if (!Hash::check($request->old, $user->password)) {
			return Controller::responder(false, "The old password you entered does not match what we have in our database. Please try again.");
		} else {
			$user->password = Hash::make($request->password);
			$user->save();
			
			return Controller::responder(true, "Your password has been changed successfully.", $user);
		}
	}
	
	public function forgotPassword (Request $request) {
		$user = User::where("email", $request->email)->first();
		if ($user) {
			$password = $this->generateRandomString(12);
			$user->password = Hash::make($password);
			$user->save();
			Mail::to($user->email)->send(new PasswordForgotten($user, $password));
			return Controller::responder(true, "Successfully sent password to your email address.", $user);
		} else {
			return Controller::responder(false, "The email address you provided is not in our records. Please make sure you entered the correct email address.", $user);
		}
		
	}

	function generateRandomString($length = 10) {
	    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
	}
	
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = User::find($request->id);
        if ($user) {
            $user->delete();
            return Controller::responder(true, "The user has been deleted successfully.", $user);
        } else {
            return Controller::responder(false, "The user you are trying to delete does not exist.", []);
        }
    }

    /* @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function uploadFile(Request $request)
    {
        $image_extensions = ["jpg", "jpeg", "png", "gif", "svg", 'webp'];
        $audio_extensions = ["mp3", "ogg", "wav"];
        $video_extensions = ["mp4", "webm", "ogg"];
        $file = $request->file('file');
        if (in_array($file->getClientOriginalExtension(), $image_extensions)) {
            $path = $file->store('profile-pic');
        } elseif (in_array($file->getClientOriginalExtension(), $audio_extensions)) {
            $path = $file->store("audios");
        } elseif (in_array($file->getClientOriginalExtension(), $video_extensions)) {
            $path = $file->store("videos");
        }
        return Controller::responder(true, "The file has been uploaded successfully. ".$file->getClientOriginalExtension(), $path);
    }

    /* @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function uploadMessageFile(Request $request)
    {
        $image_extensions = ["jpg", "jpeg", "png", "gif", "svg", "webp"];
        $audio_extensions = ["mp3", "ogg"];
        $video_extensions = ["mp4", "webm", "ogg"];
        $file = $request->file('file');
        $path = $file->store('message-files');
        
        return Controller::responder(true, "The file has been uploaded successfully.", $path);
    }

    public function getRandom () {
        $n = 20;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
     
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
     
        return $randomString;
    }
}
