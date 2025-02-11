<?php

namespace App\Http\Controllers\School\Activity;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdmissionStudentResource;
use App\Models\AdmissionStudent;
use App\Models\AdmissionStudentStage;
use App\Models\Profile;
use App\Models\Student;
use App\Models\StudentGuardian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Ramsey\Uuid\Uuid;

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

            if (request('status') == 'ENROLLED') {
                $message = 'Berhasil menyelesaikan pendaftaran. Siswa sudah ditempatkan disekolah';

                $profile_created = Profile::create([
                    'name' => $admission_student->name,
                    'birth_place' => $admission_student->birth_place,
                    'birth_date' => $admission_student->birth_date,
                    'gender' => $admission_student->gender,
                    'blood_type' => $admission_student->blood_type,
                    'religion' => $admission_student->religion,
                    'phone' => $admission_student->phone,
                    'address' => $admission_student->address,
                    'postal_code' => $admission_student->postal_code,
                ]);

                $student_created = Student::firstOrCreate([
                    'profile_id' => $profile_created->id,
                ], [
                    'school_id' => $admission_student->school_id,
                    'school_grade_id' => $admission_student->school_grade_id,
                    'school_national_id' => Uuid::uuid1(),
                ]);

                StudentGuardian::create([
                    'student_id' => $student_created->id,
                    'guardian_id' => $admission_student->transaction->customer_id,
                ]);
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
