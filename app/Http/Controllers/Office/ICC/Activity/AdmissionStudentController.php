<?php

namespace App\Http\Controllers\Office\ICC\Activity;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdmissionStudentResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SchoolResource;
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

        return Inertia::render('Office/ICC/Activity/AdmissionStudent/Detail', $data);
    }

    public function updateVerification() {}
}
