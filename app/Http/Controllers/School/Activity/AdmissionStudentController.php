<?php

namespace App\Http\Controllers\School\Activity;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdmissionStudentResource;
use App\Models\AdmissionStudent;
use App\Models\AdmissionStudentStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdmissionStudentController extends Controller
{
    public function index()
    {
        $admission_students = AdmissionStudent::whereNotIn('status', ['DRAFT', 'PENDING', 'UNVERIFIED']);

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

        return Inertia::render('School/Activity/AdmissionStudent/Index', $data);
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

        return Inertia::render('School/Activity/AdmissionStudent/Detail', $data);
    }

    public function updateStage()
    {
        DB::beginTransaction();

        try {
            $admission_student_stage = AdmissionStudentStage::where('uuid', request('admission_student_stage'))->firstOrFail();

            $admission_student_stage->update([
                'description' => request('description'),
                'status' => request('status')
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Tahapan diperbarui',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function updateStatus()
    {
        DB::beginTransaction();

        try {
            $admission_student = AdmissionStudent::where('registration_number', request('registration_number'))->firstOrFail();

            $admission_student->update([
                'status' => request('status'),
            ]);

            $message = request('status') == 'ACCEPTED' ? 'Berhasil menerima sebagai Siswa' : 'Berhasil menolak calon Siswa';

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
