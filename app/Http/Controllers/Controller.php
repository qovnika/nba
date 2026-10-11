<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function responder ($status, $message, $obj = null) {
        return response ([
            "status" => $status,
            "message" => $message,
            "data" => $obj
        ]);
    }
}
