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
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
// use Kreait\Firebase\Factory;
use App\Models\Blog;
use App\Models\Post;
use App\Models\Comment;
class MobileApiController extends Controller
{

    //register with firebase otp
    // public function register(Request $request)
    // {
    //     $request->validate([
    //         'firebase_token' => 'required',
    //         'first_name' => 'required',
    //         'last_name' => 'required',
    //         'email' => 'required|email|unique:users,email',
    //         'birthdate' => 'required|date',
    //     ]);

    //     try {
    //         $factory = (new Factory)->withServiceAccount(storage_path('app/firebase.json'));
    //         $auth = $factory->createAuth();

    //         $verifiedIdToken = $auth->verifyIdToken($request->firebase_token);

    //         $uid = $verifiedIdToken->claims()->get('sub');
    //         $phone = $verifiedIdToken->claims()->get('phone_number');

    //         // تحقق من الرقم
    //         if (User::where('phone', $phone)->exists()) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'رقم الهاتف مسجل بالفعل'
    //             ], 400);
    //         }

    //         DB::beginTransaction();

    //         $user = User::create([
    //             'first_name' => $request->first_name,
    //             'last_name' => $request->last_name,
    //             'phone' => $phone,
    //             'email' => $request->email,
    //             'birthdate' => $request->birthdate,
    //             'club_id' => 1,
    //         ]);

    //         $token = JWTAuth::fromUser($user);

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'تم إنشاء الحساب',
    //             'token' => $token
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'OTP غير صحيح',
    //             'error' => $e->getMessage()
    //         ], 401);
    //     }
    // }

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

            'birthdate.required' => 'تاريخ الميلاد مطلوب',
            'birthdate.date' => 'صيغة التاريخ غير صحيحة',
            'birthdate.before' => 'تاريخ الميلاد غير صحيح',

        ];

        $data = $request->validate([
            'phone' => 'required|numeric|unique:users,phone',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'birthdate' => 'required|date|before:today',
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
                'birthdate' => $data['birthdate'],
                'club_id' => 1,
            ]);

        
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

    //login with firebase otp
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'firebase_token' => 'required',
    //     ]);

    //     try {
    //         $factory = (new Factory)->withServiceAccount(storage_path('app/firebase.json'));
    //         $auth = $factory->createAuth();

    //         $verifiedIdToken = $auth->verifyIdToken($request->firebase_token);
    //         $phone = $verifiedIdToken->claims()->get('phone_number');

    //         $user = User::where('phone', $phone)->first();

    //         if (!$user) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'رقم الهاتف غير مسجل',
    //             ], 404);
    //         }

    //         $token = JWTAuth::fromUser($user);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'تم تسجيل الدخول',
    //             'token' => $token
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'OTP غير صحيح'
    //         ], 401);
    //     }
    // }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ], [
            'phone.required' => 'يجب إدخال رقم الهاتف',
        ]);

       
        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'رقم الهاتف غير مسجل',
            ], 404);
        }

        // ❗ هنا المفروض تتحقق من OTP (مش مفعّل دلوقتي)
        // if ($request->otp != $user->otp) { ... }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'token' => $token,
        ]);
    }



    public function user_profile_completion()
    {
        $user = auth()->guard('api_users')->user();

        return response()->json([
            'status' => true,
            'data' => [
                'profile_completion' => $user->profile_completion,
                'missing_fields' => $user->missing_fields,
            ],
        ]);
    }

    public function complete_profile(Request $request)
    {
        $user = auth()->guard('api_users')->user();

        $messages = [
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'image.image' => 'الملف يجب أن يكون صورة',
            'image.mimes' => 'الصورة يجب أن تكون png أو jpg أو jpeg أو webp',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجا',
            'birthdate.date' => 'صيغة التاريخ غير صحيحة',
        ];

        $data = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|numeric|unique:users,phone,' . $user->id,
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'birthdate' => 'nullable|date|before:today',
            'gender_id' => 'nullable|exists:genders,id',
            'country_id' => 'nullable|exists:countries,id',
            'governorate_id' => 'nullable|exists:governorates,id',
            'area_id' => 'nullable|exists:areas,id',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:10240',
        ], $messages);

        try {

            // ✅ رفع الصورة لو موجودة
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $imageName = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('users'), $imageName);

                $data['image'] = $imageName;
            }

            // ✅ تحديث المستخدم
            $user->update($data);

            // reload user
            $user = $user->fresh();

            return response()->json([
                'status' => true,
                'message' => 'تم تحديث البروفايل بنجاح',
                'data' => [
                    'user' => $user,
                    'profile_completion' => $user->profile_completion,
                    'missing_fields' => $user->missing_fields,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء تحديث البروفايل',
                'error' => $e->getMessage(),
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

    public function categories()
    {
        $data = Category::latest()->get();
        return response()->json([
                'status' => true,
                'data' => $data,
              
        ]);

    }

    public function blogs(Request $request)
    {
        $data = Blog::query()

            ->when($request->category_id, fn ($q, $v) =>
                $q->where('category_id', $v))

            ->when($request->from, fn ($q, $v) =>
                $q->whereDate('created_at', '>=', $v))

            ->when($request->to, fn ($q, $v) =>
                $q->whereDate('created_at', '<=', $v))

            ->when($request->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('title_ar', 'like', "%$search%")
                        ->orWhere('title_en', 'like', "%$search%")
                        ->orWhere('desc_ar', 'like', "%$search%")
                        ->orWhere('desc_en', 'like', "%$search%");
                });
            })

            // ✅ فلتر الترتيب
            ->when($request->order, function ($q, $order) {
                if ($order == 'oldest') {
                    $q->orderBy('created_at', 'asc');
                } else {
                    $q->orderBy('created_at', 'desc'); // default latest
                }
            }, function ($q) {
                $q->latest(); // default لو مفيش order
            })

            ->paginate(20);

        $data->getCollection()->transform(function ($data) {
            return [
                'id'  => $data->id,
                'title'=> $data->title,
                'desc'=> $data->desc,
                'image_url'=> $data->image_url,
                'category_name'=> $data->category_name,
                'category_id'=> $data->category_id,
                'created_at' => optional($data->created_at)->format('d-m-Y'),
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function blog_details($id)
    {
        $blog = Blog::findOrFail($id);

        $data = [
            'id' => $blog->id,
            'title' => $blog->title,
            'desc' => $blog->desc,
            'image_url' => $blog->image_url,
            'category_name' => $blog->category_name,
            'created_at' => optional($blog->created_at)->format('d-m-Y'),
            'views_count' => $blog->views_count,
            'is_liked' => $blog->is_liked,
        ];

        $userId = auth()->guard('api_users')->id();

        if ($userId) {

            $exists = DB::table('blog_views')
                ->where('blog_id', $blog->id)
                ->where('user_id', $userId)
                ->exists();

            if (!$exists) {
                DB::table('blog_views')->insert([
                    'blog_id' => $blog->id,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $blog->increment('views');
            }
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }


    public function toggle_like($id)
    {
        $blog = Blog::findOrFail($id);
        $userId = auth()->guard('api_users')->id();

        if (!$userId) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $like = DB::table('blog_likes')
            ->where('blog_id', $blog->id)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            DB::table('blog_likes')
                ->where('blog_id', $blog->id)
                ->where('user_id', $userId)
                ->delete();

            $liked = false;
        } else {
            DB::table('blog_likes')->insert([
                'blog_id' => $blog->id,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $liked = true;
        }

        return response()->json([
            'status' => true,
            'liked' => $liked,
            'likes_count' => $blog->likes()->count()
        ]);
    }

    public function add_post(Request $request)
    {
       

        $messages = [
            'required' => 'حقل :attribute مطلوب.',
            'image' => 'حقل :attribute يجب أن يكون صورة.',
            'mimes' => 'حقل :attribute يجب أن يكون بصيغة jpg أو jpeg أو png.',
            'max.file' => 'حقل :attribute يجب ألا يتجاوز 2 ميجا.',
           
        ];

        $attributes = [
            'image' => 'الصورة',
            'content' => 'المحتوي',
        ];

        $request->validate([
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'content' => 'required|string|max:500',
        ], $messages, $attributes);

        
        $name = null;
        if ($file = $request->file('image')) {
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('posts'), $name);
        }

        $userId = auth()->guard('api_users')->id();

        $post = new Post();
        $post->image = $name;
        $post->user_id = $userId;
        $post->content = $request->content;
        $post->save();

        return response()->json([
            'status' => true,
            'message' => 'تم الاضافة بنجاح',
        ], 200);

    }

    public function my_posts(Request $request)
    {
       
        $userId = auth()->guard('api_users')->id();

        $data =  Post::where('user_id',$userId)->latest()->paginate(10);
        $data->getCollection()->transform(function ($item) {
            return [
                'id'  => $item->id,
                'content'=> $item->content,
                'image_url'=> $item->image_url,
                'likes_count'=> $item->likes_count,
                'comments_count'=> $item->comments_count,
                'is_liked'=> $item->is_liked,
                'comments'=> $item->comments,
                'created_at' => optional($item->created_at)->format('d-m-Y'),
                'user_name'=> $item->user_name,
                'user_image_url'=> $item->user_image_url,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data,
        ], 200);

    }

    public function all_posts(Request $request)
    {
       
        $data =  Post::latest()->paginate(10);
        $data->getCollection()->transform(function ($item) {
            return [
                'id'  => $item->id,
                'content'=> $item->content,
                'image_url'=> $item->image_url,
                'likes_count'=> $item->likes_count,
                'comments_count'=> $item->comments_count,
                'is_liked'=> $item->is_liked,
                'comments'=> $item->comments,
                'created_at' => optional($item->created_at)->format('d-m-Y'),
                'user_name'=> $item->user_name,
                'user_image_url'=> $item->user_image_url,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data,
        ], 200);

    }
 
    public function add_comment(Request $request, $id)
    {
        $userId = auth()->guard('api_users')->id();

        $comment = Comment::create([
            'user_id' => $userId,
            'post_id' => $id,
            'comment' => $request->comment
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم الاضافة بنجاح',
        ]);
    }


   

}



