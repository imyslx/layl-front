<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class IndexController extends BaseController
{
    public function top()
    {
        #$summary = json_decode(file_get_contents("http://id-api.local-i.style/api/summary"));
        #return view('top', [ 'summary' => $summary]);

        return view('index');
    }
}

