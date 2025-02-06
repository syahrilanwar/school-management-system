<?php

namespace App\Models;

use App\Traits\GenerateUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmissionStudent extends Model
{
    use GenerateUuid, SoftDeletes;

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function school_grade()
    {
        return $this->belongsTo(SchoolGrade::class, 'school_grade_id');
    }

    public function stages()
    {
        return $this->hasMany(AdmissionStudentStage::class, 'admission_student_id', 'id');
    }
}
