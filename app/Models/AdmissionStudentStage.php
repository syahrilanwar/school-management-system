<?php

namespace App\Models;

use App\Traits\GenerateUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmissionStudentStage extends Model
{
    use GenerateUuid, SoftDeletes;

    protected $fillable = [
        'admission_student_id',
        'admission_stage_id',
        'officer_id',
        'scheduled_at',
        'description',
        'status',
    ];

    public function admission_stage()
    {
        return $this->belongsTo(AdmissionStage::class, 'admission_stage_id');
    }
}
