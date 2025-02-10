<?php

namespace App\Http\Controllers\Office\ICC\Activity;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdmissionStudentResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SchoolResource;
use App\Models\AdmissionStage;
use App\Models\AdmissionStudent;
use App\Models\Product;
use App\Models\School;
use App\Services\AdmissionStudentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdmissionStudentController extends Controller
{
    public function index()
    {
        $admission_students = AdmissionStudent::where('status', '!=', 'DRAFT');

        if (request()->has('search')) {
            $admission_students->where('name', 'like', '%' . request('search') . '%')
                ->orWhere('registration_number', 'like', '%' . request('search') . '%');
        }

        $admission_students = $admission_students->with('school.area')
            ->with('school_grade')
            ->latest()
            ->paginate(15);

        $data = [
            'search_params' => [
                'search' => request('search'),
            ],
            'admission_students' => AdmissionStudentResource::collection($admission_students),
        ];

        return Inertia::render('Office/ICC/Activity/AdmissionStudent/Index', $data);
    }

    public function optionSchool()
    {
        $schools = School::query();

        if (request()->has('search')) {
            $schools->whereHas('area', function ($area) {
                $area->where('name', 'like', '%' . request('search') . '%');
            });
        }

        return response()->json(SchoolResource::collection($schools->with('grades')->latest()->get()), 200);
    }

    public function detail($registration_number)
    {
        $admission_student = AdmissionStudent::where('registration_number', $registration_number)
            ->with('school.area')
            ->with('school_grade')
            ->with('stages')
            ->firstOrFail();

        $data = [
            'admission_student' => AdmissionStudentResource::make($admission_student),
        ];

        return Inertia::render('Office/ICC/Activity/AdmissionStudent/Detail', $data);
    }

    public function updateVerification()
    {
        DB::beginTransaction();

        try {
            $admission_student = AdmissionStudent::where('registration_number', request('registration_number'))->firstOrFail();

            $admission_student->update(['status' => request('status')]);

            $message = request('status') == 'VERIFIED' ? 'Berhasil memverifikasi formulir' : 'Berhasil menolak formulir';

            if (request('status') == 'VERIFIED') {
                $admission_stages = AdmissionStage::whereHasMorph('model', School::class, function ($school) use ($admission_student) {
                    $school->where('id', $admission_student->school_id);
                })->get();

                foreach ($admission_stages as $admission_stage) {
                    $admission_student->stages()->create([
                        'admission_stage_id' => $admission_stage->id,
                        'status' => 'PENDING',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
