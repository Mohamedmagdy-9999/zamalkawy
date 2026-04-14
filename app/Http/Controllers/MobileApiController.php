<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Str;
use DB;

use Illuminate\Support\Carbon;

use Illuminate\Validation\ValidationException;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Country;
use App\Models\Governorate;
use App\Models\Area;
use App\Models\Gender;
use Illuminate\Support\Facades\Validator;

class MobileApiController extends Controller
{


   

    public function genders()
    {
        $data = Gender::latest()->get();
        return response()->json([
                'status' => true,
                'data' => $data,
              
        ]);

    }


    public function countries()
    {
        $data = Country::with('governorates.areas')->latest()->get();
        return response()->json([
                'status' => true,
                'data' => $data,
              
        ]);

    }

 


   

}



