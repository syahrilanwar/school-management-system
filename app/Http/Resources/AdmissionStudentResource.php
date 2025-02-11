<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AdmissionStudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $carbon = new Carbon();
        $status_label = null;

        switch ($this->status) {
            case 'DRAFT':
                $status_label = 'Draf';
                break;
            case 'PENDING':
                $status_label = 'Menunggu';
                break;
            case 'VERIFIED':
                $status_label = 'Terverifikasi';
                break;
            case 'UNVERIFIED':
                $status_label = 'Belum Terverifikasi';
                break;
            case 'WAITING_PAYMENT':
                $status_label = 'Menunggu Pembayaran';
                break;
            case 'ACCEPTED':
                $status_label = 'Diterima';
                break;
            case 'REJECTED':
                $status_label = 'Ditolak';
                break;
            case 'ACCEPTED':
                $status_label = 'Diterima';
                break;
            case 'ENROLLED':
                $status_label = 'Pendaftaran Selesai';
                break;
            default:
                $status_label = 'Tidak Diketahui';
                break;
        }

        return [
            'uuid' => $this->uuid,
            'transaction' => $this->whenLoaded('transaction', fn() => TransactionResource::make($this->transaction)),
            'school' => $this->whenLoaded('school', fn() => SchoolResource::make($this->school)),
            'school_grade' => $this->whenLoaded('school_grade', fn() => SchoolGradeResource::make($this->school_grade)),
            'registration_number' => $this->registration_number,
            'submitted_at' => $this->submitted_at,
            'submitted_at_label' => $this->submitted_at ? $carbon->parse($this->submitted_at)->format('d-m-Y') : null,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'avatar_path' => null,
            'birth_place' => $this->birth_place,
            'birth_date' => $this->birth_date,
            'gender' => $this->gender,
            'blood_type' => $this->blood_type,
            'religion' => $this->religion,
            'languages' => $this->languages,
            'phone' => $this->phone,
            'address' => $this->address,
            'postal_code' => $this->postal_code,
            'previous_school' => $this->previous_school,
            'father_name' => $this->father_name,
            'father_birth_place' => $this->father_birth_place,
            'father_birth_date' => $this->father_birth_date,
            'father_religion' => $this->father_religion,
            'father_languages' => $this->father_languages,
            'father_phone' => $this->father_phone,
            'father_email' => $this->father_email,
            'father_address' => $this->father_address,
            'father_occupation' => $this->father_occupation,
            'father_company' => $this->father_company,
            'father_position' => $this->father_position,
            'father_national_id' => $this->father_national_id,
            'mother_name' => $this->mother_name,
            'mother_birth_place' => $this->mother_birth_place,
            'mother_birth_date' => $this->mother_birth_date,
            'mother_religion' => $this->mother_religion,
            'mother_languages' => $this->mother_languages,
            'mother_phone' => $this->mother_phone,
            'mother_email' => $this->mother_email,
            'mother_address' => $this->mother_address,
            'mother_occupation' => $this->mother_occupation,
            'mother_company' => $this->mother_company,
            'mother_position' => $this->mother_position,
            'mother_national_id' => $this->mother_national_id,
            'emergency_name' => $this->emergency_name,
            'emergency_phone' => $this->emergency_phone,
            'emergency_home_phone' => $this->emergency_home_phone,
            'emergency_relation' => $this->emergency_relation,
            'need_vehicle' => $this->need_vehicle,
            'responsible_fee' => $this->responsible_fee,
            'payer_name' => $this->payer_name,
            'payer_home_phone' => $this->payer_home_phone,
            'payer_phone' => $this->payer_phone,
            'payer_email' => $this->payer_email,
            'payer_relation' => $this->payer_relation,
            'payer_address' => $this->payer_address,
            'payer_company' => $this->payer_company,
            'payer_company_letter' => $this->payer_company_letter,
            'health_info_remark' => $this->health_info_remark,
            'health_relate_family' => $this->health_relate_family,
            'use_insurance' => $this->use_insurance,
            'use_insurance_label' => $this->use_insurance  ? 'Ya' : 'Tidak',
            'insurance_name' => $this->insurance_name,
            'insurance_network' => $this->insurance_network,
            'recomended_hospital' => $this->recomended_hospital,
            'doctor_name' => $this->doctor_name,
            'families' => $this->families,
            'questionnaires' => $this->questionnaires,
            'school_info_source' => $this->school_info_source,
            'status' => $this->status,
            'status_label' => $status_label,
            'updated_at' => $this->updated_at,
            'stages' => $this->whenLoaded('stages', fn() => AdmissionStudentStageResource::collection($this->stages)),
        ];
    }
}
