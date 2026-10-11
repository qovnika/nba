<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTitleRequest;
use App\Http\Requests\UpdateTitleRequest;
use App\Models\Title;
use Illuminate\Http\Request;

class TitleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->id) {
            $title = Title::find($request->id);
            return Controller::responder(true, "Successfully retrieved title.", $title);
        } else {
            $titles = Title::all();
            return Controller::responder(true, "Successfully retrieved titles.", $titles);
        }
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
    public function store(StoreTitleRequest $request)
    {
        $request->validate([
            'title' => ['required', 'string'],
            'due' => ['required'],
        ]);

        $title = Title::create([
            "title" => $request->title,
            "description" => $request->description,
            "due" => $request->due,
            "registration" => $request->registration
        ]);

        return Controller::responder(true, "Successfully created title.", $title);
    }

    /**
     * Display the specified resource.
     */
    public function show(Title $title)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Title $title)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTitleRequest $request, Title $title)
    {
        $request->validate([
            'title' => ['required', 'string'],
            'due' => ['required'],
        ]);

        $title = Title::find($request->id);
        $title->title = $request->title;
        $title->due = $request->due;
        $title->registration = $request->registration;
        $title->description = $request->description;
        $title->save();

        return Controller::responder(true, "Successfully updated title.", $title);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Title $title)
    {
        //
    }
}