<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\HospitalApiService;

class HospitalController extends Controller
{
    //
    public function __construct(
        protected HospitalApiService $apiService
    ) {}


    
}
