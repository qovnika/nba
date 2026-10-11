<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->id) {
            $subscriptions = Subscription::find($request->id);
        } elseif ($request->page && $request->user_id) {
            $subscriptions = Subscription::where("user_id", $request->user_id)->orderBy("id", "DESC")->paginate(10);
        } elseif ($request->user_id) {
        	$subscriptions = Subscription::where("user_id", $request->user_id)->orderBy("id", "DESC")->get();
        } else {
            $subscriptions = Subscription::with("user")->orderBy("id", "DESC")->paginate(10);
        }

        return Controller::responder(true, "Successfully retrieved subscriptions.", $subscriptions);
    }

    public function searchTransactions (Request $request) {
    	$users = User::where("fname", "LIKE", "%".$request->filter."%")->orWhere("lname", "LIKE", "%".$request->filter."%")->orWhere("scn",  "LIKE", "%".$request->filter."%")->paginate(20);
        return Controller::responder(true, "Successfully retrieved filtered user subscriptions.", $users);
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubscriptionRequest $request)
    {
        $request->validate([
            "tx_ref" => "required",
            "user_id" => "required|exists:users,id",
            "amount" => "required|numeric",
        ]);

        $sub = Subscription::updateOrCreate(
            ["tx_ref" => $request->tx_ref],
            [
                "user_id" => $request->user_id,
                "amount" => (float) $request->amount,
                "due" => $request->due ?? 0,
                "registration" => $request->registration ?? 0,
                "status" => $request->status ?? "Successful",
            ]
        );

        return Controller::responder(true, "Successfully added subscription.", $sub);
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscription $subscription)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubscriptionRequest $request, Subscription $subscription)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription)
    {
        //
    }
}
