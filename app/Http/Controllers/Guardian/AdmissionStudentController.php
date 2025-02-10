<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdmissionStudentResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SchoolResource;
use App\Models\AdmissionStudent;
use App\Models\AdmissionStudentStage;
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
        $admission_students = AdmissionStudent::whereHas('transaction', function ($transaction) {
            $transaction->where('customer_id', Auth::user()->id);
        });

        if (request()->has('search')) {
            $admission_students->where('name', 'like', '%' . request('search') . '%')
                ->orWhere('registration_number', 'like', '%' . request('search') . '%')
                ->orWhereHas('transaction', function ($transaction) {
                    $transaction->where('reference_number', 'like', '%' . request('search') . '%');
                });
        }

        $admission_students = $admission_students->with('transaction')
            ->with('school.area')
            ->with('school_grade')
            ->latest()
            ->paginate(15);

        $product = Product::where('code', 'ADMISSION_STUDENT_FORM')->first();

        $data = [
            'search_params' => [
                'search' => request('search'),
            ],
            'product' => ProductResource::make($product),
            'admission_students' => AdmissionStudentResource::collection($admission_students),
        ];

        return Inertia::render('Guardian/AdmissionStudent/Index', $data);
    }

    public function checkout()
    {
        $admissionStudentService = new AdmissionStudentService();

        DB::beginTransaction();

        try {
            $processCheckout = $admissionStudentService->processCheckout(request()->all());

            DB::commit();

            return response()->json([
                'status' => 'success',
                'transaction_payment' => $processCheckout['transaction_payment'],
                'snap_token' => $processCheckout['snap_token'],
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function optionSchool()
    {
        $schools = School::query();

        if (request()->has('search')) {
            $schools->whereHas('area', function ($area) {
                $area->where('name', 'like', '%' . request('search') . '%');
            });
        }

        return response()->json(SchoolResource::collection($schools->with('area')->with('grades')->latest()->get()), 200);
    }

    public function form($registration_number)
    {
        $admission_student = AdmissionStudent::whereHas('transaction', function ($transaction) {
            $transaction->where('customer_id', Auth::user()->id);
        })->where('registration_number', $registration_number)
            ->with('school.area')
            ->with('school_grade')
            ->firstOrFail();

        $data = [
            'admission_student' => AdmissionStudentResource::make($admission_student),
        ];

        return Inertia::render('Guardian/AdmissionStudent/Form', $data);
    }

    public function detail($registration_number)
    {
        $admission_student = AdmissionStudent::whereHas('transaction', function ($transaction) {
            $transaction->where('customer_id', Auth::user()->id);
        })->where('registration_number', $registration_number)
            ->with('school.area')
            ->with('school_grade')
            ->with('stages')
            ->firstOrFail();

        $data = [
            'admission_student' => AdmissionStudentResource::make($admission_student),
        ];

        return Inertia::render('Guardian/AdmissionStudent/Detail', $data);
    }

    public function submit()
    {
        $data = json_decode(request('full_form_data'), true);

        DB::beginTransaction();

        try {
            $admission_student = AdmissionStudent::where('uuid', $data['admission_student_id'])->first();

            if (!$admission_student) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pendaftaran siswa tidak ditemukan.'
                ], 404);
            }

            if (request()->hasFile('avatar_file')) {
                $file = request()->file('avatar_file');

                $filename = 'avatar' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                $file->storeAs('avatars', $filename, 'public');

                if ($admission_student->avatar) {
                    Storage::disk('public')->delete('avatars/' . $admission_student->avatar);
                }

                $data['avatar'] = $filename;
            }

            $admission_student->update($data);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Terima kasih! Pendaftaran Anda telah berhasil disimpan.'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function send()
    {
        DB::beginTransaction();

        try {
            $admission_student = AdmissionStudent::where('registration_number', request('registration_number'))->firstOrFail();

            $admission_student->update(['status' => 'PENDING']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Formulir berhasil dikirim',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function setSchedule()
    {
        DB::beginTransaction();

        try {
            $admission_student_stage = AdmissionStudentStage::where('uuid', request('admission_student_stage'))->firstOrFail();

            $admission_student_stage->update(['scheduled_at' => request('scheduled_at')]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Jadwal telah diatur',
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
