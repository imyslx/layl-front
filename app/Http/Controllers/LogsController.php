<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\DynamoDB\Controllers\DdbController;

class LogsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function logs(Request $request)
    {
        $user = Auth::user();

    }
}
