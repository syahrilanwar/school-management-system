<?php

namespace App\Http\Controllers\Guardian\AdmissionStudent;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdmissionStudentResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SchoolResource;
use App\Models\AdmissionStudent;
use App\Models\Product;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function purchase()
    {
        // 
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
}
