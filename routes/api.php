<?php

use App\Http\Controllers\API\ApiController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// create api here


// (login)
Route::post('login', [ApiController::class, 'login']);

Route::middleware(['auth:api'])->group(function () {
    // Route::prefix('admin')->group(function () {

        Route::get('get_onboard_screen',[ApiController::class,'getOnboardScreen']);

        Route::get('get_compus_detail',[ApiController::class,'getCompusDetail']);

        Route::get('get_course_category',[ApiController::class,'getCourseCategory']);

        Route::get('get_course_category_detail',[ApiController::class,'getCourseCategoryDetail']);

        Route::get('get_department',[ApiController::class,'getDepartment']);

        Route::get('get_grade',[ApiController::class,'getGrade']);

        Route::get('get_student_report',[ApiController::class,'getStudentReport']);

        Route::get('get_recruitment',[ApiController::class,'getRecruitment']);

        Route::get('get_recruitment_detail',[ApiController::class,'getRecruitmentDetail']);

        Route::get('get_news',[ApiController::class,'getNews']);

        Route::get('get_news_detail',[ApiController::class,'getNewsDetail']);

        // (get_config)
        Route::get('get_config', [ApiController::class, 'getConfig']);
        // (get_compus)
        Route::get('get_compus', [ApiController::class, 'getCompus']);
        // (get_category)
        Route::get('get_category', [ApiController::class, 'getCategory']);
        // (get_promotion)
        Route::get('get_promotion', [ApiController::class, 'getPromotion']);
        // (get_promotion_detail)
        Route::get('get_promotion_detail', [ApiController::class, 'getPromotionDetail']);
        // (get_event)
        Route::get('get_event', [ApiController::class, 'getEvent']);
        // (get_event_detail)
        Route::get('get_event_detail', [ApiController::class, 'getEventDetail']);
        // (get_user)
        Route::get('get_user', [ApiController::class, 'getUser']);
        // (get_app_menu)
        Route::get('get_app_menu', [ApiController::class, 'getAppMenu']);
        // (get_baner_slider)
        Route::get('get_baner_slider', [ApiController::class, 'getBanerSlider']);
    // });
    // (logout)
    Route::get('logout', [ApiController::class, 'logout']);
});
