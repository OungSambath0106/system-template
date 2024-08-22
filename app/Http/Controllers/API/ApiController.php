<?php

namespace App\Http\Controllers\API;

use App\Models\Grade;
use App\Models\Compus;
use App\Models\Onboard;
use App\Models\Student;
use App\Models\Department;
use App\Models\Recruitment;
use Illuminate\Http\Request;
use App\Models\CourseCategory;
use App\Http\Controllers\Controller;
use App\Models\Baner;
use App\Models\News;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Promotion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    public function getOnboardScreen()
    {
        $onboards = Onboard::where('status', 1)
            ->orderBy('sort_order')
            ->get();

        if ($onboards->isEmpty()) {
            return response()->json(['message' => 'No Record Found'], 200);
        }

        return response()->json($onboards, 200);
    }

    public function getCompusDetail(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $id = $request->input('id');

        $compus = Compus::where('id', $id)->where('status', 1)->first();

        if ($compus === null) {
            return response()->json(['message' => 'No Record Found'], 200);
        }

        if ($compus->count() > 0) {
            $filteredCompus = [
                'id' => $compus->id,
                'name' => $compus->name,
                'phone' => $compus->phone,
                'telegram' => $compus->telegram,
                'facebook_name' => $compus->facebook_name,
                'email' => $compus->email,
                'address' => $compus->address,
                'google_map_url' => $compus->google_map_url,
                'description' => $compus->description,
                'image' => $compus->image,
                'admission_image' => $compus->admission_image,
                'image_url' => $compus->image_url,
                'admission_image_url' => $compus->admission_image_url,
            ];
            return response()->json($filteredCompus, 200);
        }
    }

    public function getAppMenu()
    {
        $app_menu = Menu::where('status', 1)->get();

        if ($app_menu->isEmpty()) {
            return response()->json(['message' => 'No Record Found'], 200);
        }

        return response()->json($app_menu, 200);
    }

    public function getBanerSlider()
    {
        $baner_slider = Baner::where('status', 1)->get();

        if ($baner_slider->isEmpty()) {
            return response()->json(['message' => 'No Record Found'], 200);
        }

        return response()->json($baner_slider, 200);
    }

    public function getCourseCategory()
    {
        $course_categories = CourseCategory::all();

        if ($course_categories->isEmpty()) {
            return response()->json(['message' => 'No Record Found'], 200);
        }

        return response()->json($course_categories, 200);
    }

    public function getCourseCategoryDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $id = $request->input('id');

        $categorydetail =  CourseCategory::where('id', $id)->first();

        if ($categorydetail === null) {
            return response()->json(['message' => 'No Record Found'], 200);
        }
        $filteredcategorydetail = [
            'id' => $categorydetail->id,
            'title' => $categorydetail->title,
            'curriculum_image' => $categorydetail->curriculum_image,
            'assessment_detail' => $categorydetail->assessment_detail,
            'icon' => $categorydetail->icon,
            'curriculum_image_url' => $categorydetail->curriculum_image_url,
            'assessment_detail_url' => $categorydetail->assessment_detail_url,
            'icon_url' => $categorydetail->icon_url,
            'description' => $categorydetail->description,
        ];
        return response()->json($filteredcategorydetail, 200);
    }

    public function getConfig(Request $request)
    {
        // $config = new BusinessSetting;
        // $data['company_name'] = @$config->where('type', 'company_name')->first()->value;
        // $data['app_logo'] = @$config->where('type', 'web_header_logo')->first()->value;
        // $data['app_logo'] = asset('uploads/business_settings/' . $data['app_logo']);
        // $data['help_desk_banner'] = @$config->where('type', 'help_desk_banner')->first()->value;
        // $data['help_desk_banner'] = asset('uploads/business_settings/' . $data['help_desk_banner']);
        // $data['help_desk_description'] = @$config->where('type', 'help_desk_description')->first()->value;
        // return response()->json($data);

        $configs = BusinessSetting::all();

        $data = [];

        foreach ($configs as $config) {
            $data[$config->type] = $config->value;

            if (in_array($config->type, ['language', 'pnc_language'])) {
                $data[$config->type] = json_decode($config->value, true);
            }

            if (in_array($config->type, ['web_header_logo', 'contact_us_logo', 'help_desk_banner'])) {
                if ($config->value && file_exists('uploads/business_settings/' . $config->value)) {
                    $data[$config->type] = asset('uploads/business_settings/' . $config->value);
                } else {
                    asset('uploads/image/default.png');
                }

            }
        }

        return response()->json($data);
    }

    public function getCompus(Request $request)
    {
        $compuses = Compus::where('status', '1')->get();

        if ($compuses->isEmpty()) {
            return response()->json(['message' => 'No records found'], 404);
        }

        return response()->json($compuses, 200);
    }

    public function getCategory(Request $request)
    {
        $categories = Category::where('status', '1')
            ->select('name', 'slug')
            ->get();

        if ($categories->isEmpty()) {
            return response()->json(['message' => 'No records found'], 404);
        }

        return response()->json($categories, 200);
    }

    public function getPromotion(Request $request)
    {
        $currentDate = Carbon::now();

        $promotion = Promotion::where('status', '1')
            ->whereDate('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->select('id', 'title', 'short_description', 'content', 'header_banner', 'footer_banner', 'start_date', 'end_date')
            ->get();

        if ($promotion->isEmpty()) {
            return response()->json(['message' => 'No records found'], 404);
        }

        return response()->json($promotion, 200);
    }

    public function getPromotionDetail(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $id = $request->input('id');

        $promotion = Promotion::select('title', 'short_description', 'content', 'header_banner', 'footer_banner', 'start_date', 'end_date')
            ->where('id', $id)
            ->where('status', 1)
            ->first();

        if (!$promotion) {
            return response()->json(['error' => 'Promotion not found'], 404);
        }

        return response()->json($promotion, 200);
    }

    public function getDepartment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'compus_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $compus_id = $request->input('compus_id');

        $departments = Department::where('compus_id', $compus_id)
            ->where('status', 1)
            ->orderBy('order')
            ->get();

        if ($departments->isEmpty()) {
            return response()->json(['message' => 'No Record Found'], 200);
        }

        return response()->json($departments, 200);
    }

    public function getGrade(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $department_id = $request->input('department_id');

        $grades = Grade::where('department_id', $department_id)
            ->where('status', 1)
            ->orderBy('order')
            ->get();

        if ($grades->isEmpty()) {
            return response()->json(['message' => 'No Record Found'], 200);
        }

        return response()->json($grades, 200);
    }

    public function getStudentReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'grade_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $grade_id = $request->input('grade_id');

        $studentreports = Student::where('grade_id', $grade_id)
            ->where('status', 1)
            ->get();

        if ($studentreports->isEmpty()) {
            return response()->json(['message' => 'No Record Found'], 200);
        }

        return response()->json($studentreports, 200);
    }

    public function getRecruitment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'compus_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $compus_id = $request->input('compus_id');
        $recruitments = Recruitment::where('compus_id', $compus_id)
            ->where('status', 1)
            ->get(); // Use get() to retrieve all matching records

        if ($recruitments->isEmpty()) {
            return response()->json(['message' => 'No Record Found'], 200);
        }

        return response()->json($recruitments, 200);
    }

    public function getRecruitmentDetail(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $id = $request->input('id');
        $recruitmentdetails = Recruitment::where('id', $id)->where('status', 1)->first();

        if ($recruitmentdetails === null) {
            return response()->json(['message' => 'No Record Found'], 200);
        }

        if ($recruitmentdetails->count() > 0) {
            $filteredrecruitmentdetail =
                [
                    'id' => $recruitmentdetails->id,
                    'title' => $recruitmentdetails->title,
                    'description' => $recruitmentdetails->description,
                    // 'compus_id' => $recruitmentdetails->compus_id,
                    'image' => $recruitmentdetails->image,
                    'image_url' => $recruitmentdetails->image_url,

                ];
            return response()->json($filteredrecruitmentdetail, 200);
        }
    }

    public function getNews()
    {
        $news = News::where('type', 'news')
            ->where('status', 1)
            ->get();

        if ($news->isEmpty()) {
            return response()->json(['message' => 'No Record Found'], 200);
        }

        return response()->json($news, 200);
    }

    public function getNewsDetail(Request $request)
    {

        $validator = validator::make($request->all(), [
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $id = $request->input('id');

        $newdetails = News::where('id', $id)->where('status', 1)->first();

        if ($newdetails === null) {
            return response()->json(['message' => 'No Record Found'], 200);
        }
        if ($newdetails->count() > 0) {
            $filterednewdetail =
                [
                    'id' => $newdetails->id,
                    'title' => $newdetails->title,
                    'type' => $newdetails->type,
                    'thumbnail' => $newdetails->thumbnail,
                    'thumbnail_url' => $newdetails->thumbnail_url,
                    'content' => $newdetails->content,
                ];

            return response()->json($filterednewdetail, 200);
        }
    }

    public function getEvent()
    {
        $event = News::where('type', 'event')
            ->where('status', 1)
            ->get();

        if ($event->isEmpty()) {
            return response()->json(['message' => 'No Record Found'], 200);
        }

        return response()->json($event, 200);
    }

    public function getEventDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $id = $request->input('id');

        $event = News::select('title', 'type', 'thumbnail', 'content')
            ->where('id', $id)
            ->where('status', 1)
            ->first();

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        return response()->json($event, 200);
    }

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('name', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->hasRole('admin')) {
                try {
                    $token = $user->createToken('accessToken')->accessToken;

                    return response()->json([
                        'access_token' => $token,
                        'user' => $user,
                        'message' => 'Login successful',
                    ], 200);
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Token generation failed',
                    ], 500);
                }
            } else {
                Auth::logout();
                return response()->json([
                    'message' => 'Permission denied. Only admin can login',
                ], 403);
            }
        } else {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }
    }

    public function getUser(Request $request)
    {
        $id = auth()->user()->id;
        $user = User::findOrFail($id);

        if (!$user) {
            return response()->json(['message' => 'No records found'], 404);
        }

        return response()->json($user, 200);
    }


    public function logout(Request $request)
    {
        if (Auth::check()) {
            $request->user()->token()->revoke();

            return response()->json(['message' => 'Logged out successfully'], 200);
        }

        return response()->json(['message' => 'Logout failed'], 403);
    }
}
