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


    public function register(Request $request)
    {
        // ✅ رسائل عربية
        $messages = [
            
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.numeric' => 'رقم الهاتف يجب أن يكون أرقام فقط',
            'phone.unique' => 'رقم الهاتف مستخدم من قبل',

            'first_name.required' => 'الاسم مطلوب',
            'first_name.max' => 'الاسم طويل جدًا',

            'last_name.required' => 'الاسم مطلوب',
            'last_name.max' => 'الاسم طويل جدًا',

            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.unique' => 'البريد الإلكتروني مستخدم من قبل',

            'image.image' => 'الملف يجب أن يكون صورة',
            'image.mimes' => 'الصورة يجب أن تكون png أو jpg أو jpeg أو webp',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجا',

        ];

        $data = $request->validate([
            'phone' => 'required|numeric|unique:users,phone',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:10240',
        ], $messages);

        DB::beginTransaction();

        try {

            // ✅ رفع الصورة
            $imageName = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $imageName = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('users'), $imageName);
            }

            // ✅ إنشاء المواطن
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'image' => $imageName,
                'club_id' => 1,
            ]);

            // ✅ Auto login + JWT
            $token = Auth::guard('api_users')->login($user);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'تم إنشاء الحساب بنجاح',
                'guard' => 'api_users',
                'token' => $token,
              
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء إنشاء الحساب',
                'error' => $e->getMessage(), // احذفها في الإنتاج
            ], 500);
        }
    }
   
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



