<?php

use App\Models\Category;
use App\Models\MenuCategory;
use App\helpers\ImageManager;
use App\Models\BoothCategory;
use App\Models\BusinessSetting;
use App\Models\PartnerCategory;
use Illuminate\Routing\RouteGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\VisitorController;
use App\Http\Controllers\Backends\BlogController;
use App\Http\Controllers\Backends\RoleController;
use App\Http\Controllers\Backends\UserController;
use App\Http\Controllers\Backends\BoothController;
use App\Http\Controllers\Backends\EventController;
use App\Http\Controllers\Backends\MediaController;
use App\Http\Controllers\Backends\MovieController;
use App\Http\Controllers\Backends\GradeController;
use App\Http\Controllers\Backends\BanerController;
use App\Http\Controllers\Backends\NoticeController;
use App\Http\Controllers\Backends\SliderController;
use App\Http\Controllers\Backends\CompusController;
use App\Http\Controllers\Backends\StudentController;
use App\Http\Controllers\Backends\ProductController;
use App\Http\Controllers\Backends\CategoryController;
use App\Http\Controllers\Backends\LanguageController;
use App\Http\Controllers\Backends\DashboardController;
use App\Http\Controllers\Backends\DepartmentController;
use App\Http\Controllers\Backends\NewsletterController;
use App\Http\Controllers\Backends\EventDetailController;
use App\Http\Controllers\Backends\FileManagerController;
use App\Http\Controllers\Backends\MenuCategoryController;
use App\Http\Controllers\Backends\PhotoGalleryController;
use App\Http\Controllers\Backends\BoothCategoryController;
use App\Http\Controllers\Backends\BusinessSettingController;
use App\Http\Controllers\Backends\CourseCategoryController;
use App\Http\Controllers\Backends\MenuController as BackendsMenuController;
use App\Http\Controllers\Backends\NewsController as BackendsNewsController;
use App\Http\Controllers\Backends\OnboardController;
use App\Http\Controllers\Backends\PartnerCategoryController;
use App\Http\Controllers\Backends\PromotionController;
use App\Http\Controllers\Backends\RecruitmentController;
use App\Http\Controllers\Backends\ServiceForVisitorController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\NewsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// change language
Route::get('language/{locale}', function ($locale) {
    app()->setLocale($locale);
    session()->put('locale', $locale);
    $language = \App\Models\BusinessSetting::where('type', 'language')->first();
    session()->put('language_settings', $language);
    return redirect()->back();
})->name('change_language');

Auth::routes();

// save temp file
Route::post('save_temp_file', [FileManagerController::class, 'saveTempFile'])->name('save_temp_file');

Route::redirect('/', '/admin/dashboard');

Route::post('save_temp_file', [FileManagerController::class, 'saveTempFile'])->name('save_temp_file');
Route::get('remove_temp_file', [FileManagerController::class, 'removeTempFile'])->name('remove_temp_file');

// back-end
Route::middleware(['auth', 'CheckUserLogin', 'SetSessionData'])->group(function () {

    Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // setting
        Route::group(['prefix' => 'setting', 'as' => 'setting.'], function () {
            Route::get('/', [BusinessSettingController::class, 'index'])->name('index');
            Route::put('/update', [BusinessSettingController::class, 'update'])->name('update');

            // language setup
            Route::group(['prefix' => 'language', 'as' => 'language.'], function () {
                Route::get('/', [LanguageController::class, 'index'])->name('index');
                Route::get('/create', [LanguageController::class, 'create'])->name('create');
                Route::post('/', [LanguageController::class, 'store'])->name('store');
                Route::get('/edit', [LanguageController::class, 'edit'])->name('edit');
                Route::put('/update', [LanguageController::class, 'update'])->name('update');
                Route::delete('delete/', [LanguageController::class, 'delete'])->name('delete');

                Route::get('/update-status', [LanguageController::class, 'updateStatus'])->name('update-status');
                Route::get('/update-default-status', [LanguageController::class, 'update_default_status'])->name('update-default-status');
                Route::get('/translate', [LanguageController::class, 'translate'])->name('translate');
                Route::post('translate-submit/{lang}', [LanguageController::class, 'translate_submit'])->name('translate.submit');
            });
        });

        Route::get('user/filter', [UserController::class, 'filter'])->name('user.filter');
        Route::get('user/update_status', [UserController::class, 'updateStatus'])->name('user.update_status');
        Route::resource('user', UserController::class);

        Route::get('product-category/update_status', [CategoryController::class, 'updateStatus'])->name('product-category.update_status');
        Route::resource('product-category', CategoryController::class);

        Route::resource('product', ProductController::class);

        Route::resource('course-category', CourseCategoryController::class);

        Route::resource('news', BackendsNewsController::class);
        Route::post('news/update_status', [BackendsNewsController::class, 'updateStatus'])->name('news.update_status');

        Route::resource('menu', BackendsMenuController::class);
        Route::post('menu/update_status', [BackendsMenuController::class, 'updateStatus'])->name('menu.update_status');

        Route::resource('recruitment', RecruitmentController::class);
        Route::post('recruitment/update_status', [RecruitmentController::class, 'updateStatus'])->name('recruitment.update_status');

        Route::resource('promotion', PromotionController::class);
        Route::post('promotion/update_status', [PromotionController::class, 'updateStatus'])->name('promotion.update_status');

        Route::resource('onboard', OnboardController::class);
        Route::post('onboard/update_status', [OnboardController::class, 'updateStatus'])->name('onboard.update_status');
        // Form Compus
        Route::get('compus/update_status', [CompusController::class, 'updateStatus'])->name('compus.update_status');
        Route::resource('compus', CompusController::class);

        // Form Department
        Route::get('department/update_status', [DepartmentController::class, 'updateStatus'])->name('department.update_status');
        Route::resource('department', DepartmentController::class);

        // Form Grade
        Route::get('grade/update_status', [GradeController::class, 'updateStatus'])->name('grade.update_status');
        Route::resource('grade', GradeController::class);

        // Form Student-Report
        Route::get('student/update_status', [StudentController::class, 'updateStatus'])->name('student.update_status');
        Route::resource('student', StudentController::class);

        // Form Baner-Slider
        Route::get('baner-slider/update_status', [BanerController::class, 'updateStatus'])->name('baner-slider.update_status');
        Route::resource('baner-slider', BanerController::class);
    });
});

Route::middleware(['auth'])->group(function () {
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});
