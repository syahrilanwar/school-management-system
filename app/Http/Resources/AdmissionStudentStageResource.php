<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdmissionStudentStageResource extends JsonResource
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
            case 'PENDING':
                $status_label = 'Menunggu';
                break;
            case 'INTERVIEW':
                $status_label = 'Wawancara';
                break;
            case 'PASSED':
                $status_label = 'Lulus';
                break;
            case 'FAILED':
                $status_label = 'Gagal';
                break;
            case 'DOING':
                $status_label = 'Sedang Berlangsung';
                break;
            case 'DONE':
                $status_label = 'Selesai';
                break;
            default:
                $status_label = 'Tidak Diketahui';
                break;
        }


        return [
            'uuid' => $this->uuid,
            'admission_student' => $this->whenLoaded('admission_student', fn() => AdmissionStudentResource::make($this->admission_student)),
            'admission_stage' => $this->whenLoaded('admission_stage', fn() => AdmissionStageResource::make($this->admission_stage)),
            'officer' => $this->whenLoaded('officer', fn() => EmployeeResource::make($this->officer)),
            'title' => $this->admission_stage->title,
            'type' => $this->admission_stage->type,
            'scheduled_at' => $this->scheduled_at,
            'scheduled_at_label' => $this->scheduled_at ? $carbon->parse($this->scheduled_at)->format('d-m-Y h:i') : 'Belum dijadwalkan',
            'description' => $this->description ?? 'Belum ada keterangan',
            'status' => $this->status,
            'status_label' => $status_label,
        ];
    }
}
