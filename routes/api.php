<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('v-mobile')->group(function () {
    
         Route::post('register', 'MobileApiController@register');
        Route::post('login', 'MobileApiController@login');

        Route::get('genders','MobileApiController@genders');
        Route::get('countries','MobileApiController@countries');

        
        Route::middleware(['auth:api_users', 'user'])->group(function () {

            Route::get('user_profile_completion','MobileApiController@user_profile_completion');
            Route::post('complete_profile','MobileApiController@complete_profile');

            Route::get('categories','MobileApiController@categories');

            Route::get('blogs','MobileApiController@blogs');
            Route::get('blog_details/{id}','MobileApiController@blog_details');
            
        });

});


Route::prefix('v-admin')->group(function () {
    
   
    Route::post('admin_login', 'AdminApiController@admin_login');
        Route::middleware(['auth:api_admins', 'admin'])->group(function () {
            Route::get('check_admin', 'AdminApiController@admin_check');
            Route::post('update_admin_profile', 'AdminApiController@update_admin_profile');
            Route::post('admin_change_password', 'AdminApiController@admin_change_password');
        
            Route::post('add_blog', 'AdminApiController@add_blog');
            Route::post('update_blog/{id}', 'AdminApiController@update_blog');
            Route::delete('delete_blog/{id}', 'AdminApiController@delete_blog');
            Route::get('blogs', 'AdminApiController@blogs');
            Route::get('single_blog/{id}', 'AdminApiController@single_blog');

            
        });

});