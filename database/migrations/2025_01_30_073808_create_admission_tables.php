<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admission_stages', function (Blueprint $table) {
            $table->id('id');
            $table->uuid('uuid');
            $table->morphs('model');
            $table->string('type'); // PRE, POST
            $table->string('title');
            $table->integer('sort_number');
            $table->timestampsTz();
            $table->softDeletes();
        });

        Schema::create('admission_students', function (Blueprint $table) {
            $table->id('id');
            $table->uuid('uuid');
            $table->foreignId('transaction_id')->constrained('transactions');
            $table->foreignId('school_id')->constrained('schools');
            $table->foreignId('school_grade_id')->constrained('school_grades');
            $table->string('registration_number')->unique();
            $table->dateTime('submitted_at')->nullable();
            $table->string('name')->nullable();
            $table->string('avatar')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('religion')->nullable();
            $table->json('languages')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('previous_school')->nullable();
            $table->string('father_name')->nullable();
            $table->string('father_birth_place')->nullable();
            $table->date('father_birth_date')->nullable();
            $table->string('father_religion')->nullable();
            $table->json('father_languages')->nullable();
            $table->string('father_phone')->nullable();
            $table->string('father_email')->nullable();
            $table->text('father_address')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('father_company')->nullable();
            $table->string('father_position')->nullable();
            $table->string('father_national_id')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_birth_place')->nullable();
            $table->date('mother_birth_date')->nullable();
            $table->string('mother_religion')->nullable();
            $table->json('mother_languages')->nullable();
            $table->string('mother_phone')->nullable();
            $table->string('mother_email')->nullable();
            $table->text('mother_address')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('mother_company')->nullable();
            $table->string('mother_position')->nullable();
            $table->string('mother_national_id')->nullable();
            $table->string('emergency_name')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->string('emergency_home_phone')->nullable();
            $table->string('emergency_relation')->nullable();
            $table->string('need_vehicle')->nullable();
            $table->string('responsible_fee')->nullable();
            $table->string('payer_name')->nullable();
            $table->string('payer_home_phone')->nullable();
            $table->string('payer_phone')->nullable();
            $table->string('payer_email')->nullable();
            $table->string('payer_relation')->nullable();
            $table->string('payer_address')->nullable();
            $table->string('payer_company')->nullable();
            $table->text('payer_company_letter')->nullable();
            $table->text('health_info_remark')->nullable();
            $table->string('health_relate_family')->nullable();
            $table->boolean('use_insurance')->default(false);
            $table->string('insurance_name')->nullable();
            $table->string('insurance_network')->nullable();
            $table->string('recomended_hospital')->nullable();
            $table->string('doctor_name')->nullable();
            $table->json('families')->nullable();
            $table->json('questionnaires')->nullable();
            $table->json('school_info_source')->nullable();
            $table->string('status'); // DRAFT, PENDING, VERIFIED, WAITING_INTERVIEW, PASSED, FAILED, WAITING_PAYMENT, ACCEPTED, CANCELED
            $table->timestampsTz();
            $table->softDeletes();
        });

        Schema::create('admission_student_stages', function (Blueprint $table) {
            $table->id('id');
            $table->uuid('uuid');
            $table->foreignId('admission_student_id')->constrained('admission_students');
            $table->foreignId('admission_stage_id')->constrained('admission_stages');
            $table->foreignId('officer_id')->nullable()->constrained('employees');
            $table->dateTime('scheduled_at')->nullable();
            $table->longText('description')->nullable();
            $table->string('status'); // PENDING, SCHEDULED, INTERVIEW, PASSED, FAILED
            $table->timestampsTz();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_student_stages');
        Schema::dropIfExists('admission_students');
        Schema::dropIfExists('admission_stages');
    }
};
