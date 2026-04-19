<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Admin;
use Str;
use DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use App\Models\Blog;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\CarbonPeriod;
class AdminApiController extends Controller
{

    public function admin_login(Request $request)
    {
        // ✅ validation
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ], [
            'email.required' => 'البريد الالكتروني مطلوب',
           
            'password.required' => 'كلمة المرور مطلوبة',
        ]);

        // ✅ تحديد credentials حسب المدخل
      
            $credentials = [
                'email' => $request->email,
                'password' => $request->password,
                
            ];
      

        // ✅ محاولة تسجيل الدخول
        if (!$token = Auth::guard('api_admins')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'login' => ['بيانات الدخول غير صحيحة'],
            ]);
        }

        $user = Auth::guard('api_admins')->user();

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'guard' => 'api_admins',
            'token' => $token,
            
        ]);
    }

    public function update_admin_profile(Request $request)
    {
        $user = Auth::guard('api_admins')->user(); // المستخدم الحالي من التوكن

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'المستخدم غير موجود أو التوكن غير صالح'
            ], 401);
        }

        $messages = [
          
            'name.max' => 'الاسم طويل جدًا',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.unique' => 'البريد الإلكتروني مستخدم من قبل',
           
        ];

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
           
            'email' => 'sometimes|email|unique:admins,email,' . $user->id,
            'password' => 'nullable|string|min:8',
        ], $messages);

       
        // تحديث كلمة المرور لو موجودة
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
           
        }

        $user->update($data);

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث البيانات بنجاح',
            'user' => $user
        ]);
    }

    public function admin_change_password(Request $request)
    {
        $user = Auth::guard('api_admins')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'المستخدم غير موجود أو التوكن غير صالح'
            ], 401);
        }

        $messages = [
            'current_password.required' => 'يجب إدخال كلمة المرور الحالية',
            'new_password.required' => 'يجب إدخال كلمة المرور الجديدة',
            'new_password.min' => 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل',
            'new_password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
        ];

        $data = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ], $messages);

        // التحقق من كلمة المرور الحالية
        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'كلمة المرور الحالية غير صحيحة'
            ], 422);
        }

        // تحديث كلمة المرور
        $user->update([
            'password' => Hash::make($data['new_password'])
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم تغيير كلمة المرور بنجاح'
        ]);
    }

    public function admin_check(Request $request)
    {
        try {

            $admin = Auth::guard('api_admins')->user();

            if (!$admin) {
                return response()->json([
                    'status' => false,
                    'message' => 'التوكن غير صالح أو انتهى'
                ], 401);
            }

            $data = [
                'id'    => $admin->id,
                'name'  => $admin->name,
                'email' => $admin->email,
                'phone' => $admin->phone,
                'guard' => 'api_admins',
            ];

            return response()->json([
                'status' => true,
                'user' => $data
            ]);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json([
                'status' => false,
                'message' => 'التوكن انتهى، الرجاء تسجيل الدخول مرة أخرى'
            ], 401);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function add_blog(Request $request)
    {
        $messages = [
            'required' => 'حقل :attribute مطلوب.',
            'image' => 'حقل :attribute يجب أن يكون صورة.',
            'mimes' => 'حقل :attribute يجب أن يكون بصيغة jpg أو jpeg أو png.',
            'max.file' => 'حقل :attribute يجب ألا يتجاوز 2 ميجا.',
            'exists' => 'القسم غير موجود.',
        ];

        $attributes = [
            'image' => 'الصورة',
            'title_ar' => 'عنوان المقال العربي',
            'title_en' => 'عنوان المقال الانجليزي',
            'desc_ar' => 'وصف المقال العربي',
            'desc_en' => 'وصف المقال الانجليزي',
            'category_id' => 'القسم',
        ];

        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category_id' => 'required|exists:categories,id',
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'desc_ar' => 'required',
            'desc_en' => 'required',
        ], $messages, $attributes);

        
        $name = null;
        if ($file = $request->file('image')) {
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('blog'), $name);
        }

        $admin = Auth::guard('api_admins')->user();

        $blog = new Blog();
        $blog->image = $name;
        $blog->category_id = $request->category_id;
        $blog->title_ar = $request->title_ar;
        $blog->title_en = $request->title_en;
        $blog->desc_ar = $request->desc_ar;
        $blog->desc_en = $request->desc_en;
        $blog->club_id = $admin->club_id;
        $blog->save();

        return response()->json([
            'status' => true,
            'message' => 'تم الاضافة بنجاح',
        ], 200);
    }

    public function update_blog(Request $request, $id)
    {
        $messages = [
            'image' => 'حقل :attribute يجب أن يكون صورة.',
            'mimes' => 'حقل :attribute يجب أن يكون بصيغة jpg أو jpeg أو png.',
            'max.file' => 'حقل :attribute يجب ألا يتجاوز 2 ميجا.',
            'exists' => 'القسم غير موجود.',
        ];

        $attributes = [
            'image' => 'الصورة',
            'title_ar' => 'عنوان المقال العربي',
            'title_en' => 'عنوان المقال الانجليزي',
            'desc_ar' => 'وصف المقال العربي',
            'desc_en' => 'وصف المقال الانجليزي',
            'category_id' => 'القسم',
        ];

        $request->validate([
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category_id' => 'required|exists:categories,id',
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'desc_ar' => 'required',
            'desc_en' => 'required',
        ], $messages, $attributes);

        
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json([
                'status' => false,
                'message' => 'المقال غير موجود'
            ], 404);
        }

       
        if ($request->hasFile('image')) {

            
            if ($blog->image && File::exists(public_path('blog/' . $blog->image))) {
                File::delete(public_path('blog/' . $blog->image));
            }

            $file = $request->file('image');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('blog'), $name);

            $blog->image = $name;
        }

        $blog->category_id = $request->category_id;
        $blog->title_ar = $request->title_ar;
        $blog->title_en = $request->title_en;
        $blog->desc_ar = $request->desc_ar;
        $blog->desc_en = $request->desc_en;
        $blog->save();

        return response()->json([
            'status' => true,
            'message' => 'تم التحديث بنجاح'
        ]);
    }

    public function delete_blog($id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json([
                'status' => false,
                'message' => 'المقال غير موجود'
            ], 404);
        }

        // حذف الصورة
        if ($blog->image && File::exists(public_path('blog/' . $blog->image))) {
            File::delete(public_path('blog/' . $blog->image));
        }

        // حذف من الداتابيز
        $blog->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم الحذف بنجاح'
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

            ->latest()
            ->paginate(20);

        $data->getCollection()->transform(function ($item) {
            return [
                'id'  => $item->id,
                'title_ar'=> $item->title_ar,
                'title_en'=> $item->title_en,
                'desc_ar'=> $item->desc_ar,
                'desc_en'=> $item->desc_en,
                'image_url'=> $item->image_url,
                'category_name'=> $item->category_name,
                'category_id'=> $item->category_id,
                'created_at' => optional($item->created_at)->format('d-m-Y'),
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function single_blog(Request $request, $id)
    {
        $item = Blog::findOrFail($id);

        $data = [
            'id'  => $item->id,
            'title'  => $item->title,
            'title_ar'=> $item->title_ar,
            'title_en'=> $item->title_en,
            'desc_ar'=> $item->desc_ar,
            'desc_en'=> $item->desc_en,
            'desc'=> $item->desc,
            'image_url'=> $item->image_url,
            'category_name'=> $item->category_name,
            'category_id'=> $item->category_id,
            'user_name'=> $item->admin_name,
            'created_at' => optional($item->created_at)->format('d-m-Y'),
        ];

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }


   


  
    
}
