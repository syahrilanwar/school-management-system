<?php

namespace Database\Seeders;

use App\Models\AdmissionStage;
use App\Models\AdmissionStudent;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\School;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

use function Pest\Laravel\json;

class DummyAdmissionStudentActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = json_decode(file_get_contents('database/data/default/options.json'));
        $questionnaires = json_decode(file_get_contents('database/data/default/questionnaires.json'));

        $faker = Factory::create();
        // Purchasing the admission form
        if (App::environment(['local', 'testing'])) {
            $customers = User::role(['System Admin', 'Site Admin', 'Guardian'])->get();
            $product = Product::where('code', 'ADMISSION_STUDENT_FORM')->first();

            $this->command->warn('Purchasing the admission form');
            $this->command->getOutput()->progressStart(count($customers));
            foreach ($customers as $customer) {
                DB::beginTransaction();

                try {
                    foreach (range(1, rand(25, 50)) as $index) {
                        $transaction = Transaction::create([
                            'customer_id' => $customer->id,
                            'type' => 'SALES',
                            'sub_type' => 'ADMISSION_STUDENT_FORM',
                            'reference_number' => 'TRX-ASF-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
                            'due_date' => now()->addDays(3),
                            'total_amount' => $product->price,
                            'discount_amount' => 0,
                            'bill_amount' => $product->price,
                        ]);

                        $school = School::inRandomOrder()->first();
                        $school_grade = $school->level->grades()->inRandomOrder()->first();
                        $module = [
                            'name' => $faker->name,
                            'birth_date' => $faker->date,
                            'school_id' => $school->id,
                            'school_grade_id' => $school_grade->id,
                        ];

                        $transaction->items()->create([
                            'product_id' => $product->id,
                            'price' => $product->price,
                            'quantity' => 1,
                            'total_amount' => $product->price,
                            'bill_amount' => $product->price,
                            'options' => [
                                'module' => $module
                            ],
                        ]);

                        $payment_status = ['PAID', 'UNPAID'];

                        $transaction->payments()->create([
                            'reference_number' => 'TRX-CUST-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
                            'type' => 'ONLINE',
                            'amount' => $transaction->bill_amount,
                            'options' => [
                                'gateway_provider' => 'MIDTRANS',
                                'gateway_token' => Str::random(20),
                                'gateway_response' => null,
                            ],
                            'status' => Arr::random($payment_status),
                        ]);

                        $paid_amount = $transaction->payments()->where('status', 'PAID')->sum('amount');
                        $transaction->paid_amount = $paid_amount;
                        $transaction->save();
                    }

                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();

                    throw $th;
                }
                $this->command->getOutput()->progressAdvance();
            }
            $this->command->getOutput()->progressFinish();
        }
        // Receiving the admission form
        if (App::environment(['local', 'testing'])) {
            $transactions = Transaction::whereColumn('bill_amount', '=', 'paid_amount')
                ->where('type', 'SALES')
                ->where('sub_type', 'ADMISSION_STUDENT_FORM')
                ->get();

            $this->command->warn('Receiving the admission form');
            $this->command->getOutput()->progressStart(count($transactions));
            foreach ($transactions as $transaction) {
                DB::beginTransaction();

                try {

                    $admission_status = ['DRAFT', 'PENDING', 'VERIFIED'];
                    $admission_status_selected = Arr::random($admission_status);
                    $module = $transaction->items()->first() ? $transaction->items()->first()->options['module'] : null;

                    $admission_student = AdmissionStudent::create([
                        'transaction_id' => $transaction->id,
                        'school_id' => $module['school_id'],
                        'school_grade_id' => $module['school_grade_id'],
                        'submitted_at' => $admission_status_selected == 'PENDING' ? now()->addDays(rand(3, 10)) : null,
                        'registration_number' => 'ADM-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
                        'name' => $module['name'],
                        'avatar' => $faker->imageUrl(200, 200, 'people'),
                        'birth_place' => $faker->city,
                        'birth_date' => $module['birth_date'],
                        'gender' => $faker->randomElement(['MALE', 'FEMALE']),
                        'blood_type' => $faker->randomElement(['A', 'B', 'AB', 'O']),
                        'religion' => $faker->randomElement($options->religions),
                        'languages' => $faker->randomElement(['Indonesian', 'English', 'Mandarin', 'Javanese']),
                        'phone' => $faker->phoneNumber,
                        'address' => $faker->address,
                        'postal_code' => $faker->postcode,
                        'previous_school' => $faker->company,
                        'father_name' => $faker->name('male'),
                        'father_birth_place' => $faker->city,
                        'father_birth_date' => $faker->date,
                        'father_religion' => $faker->randomElement($options->religions),
                        'father_languages' => $faker->randomElement(['Indonesian', 'English', 'Mandarin', 'Javanese']),
                        'father_phone' => $faker->phoneNumber,
                        'father_email' => $faker->safeEmail,
                        'father_address' => $faker->address,
                        'father_occupation' => $faker->jobTitle,
                        'father_company' => $faker->company,
                        'father_position' => $faker->jobTitle,
                        'father_national_id' => $faker->numerify('################'),
                        'mother_name' => $faker->name('female'),
                        'mother_birth_place' => $faker->city,
                        'mother_birth_date' => $faker->date,
                        'mother_religion' => $faker->randomElement($options->religions),
                        'mother_languages' => $faker->randomElement(['Indonesian', 'English', 'Mandarin', 'Javanese']),
                        'mother_phone' => $faker->phoneNumber,
                        'mother_email' => $faker->safeEmail,
                        'mother_address' => $faker->address,
                        'mother_occupation' => $faker->jobTitle,
                        'mother_company' => $faker->company,
                        'mother_position' => $faker->jobTitle,
                        'mother_national_id' => $faker->numerify('################'),
                        'emergency_name' => $faker->name,
                        'emergency_phone' => $faker->phoneNumber,
                        'emergency_home_phone' => $faker->phoneNumber,
                        'emergency_relation' => $faker->randomElement(['UNCLE', 'AUNT', 'GRANDPARENT', 'SIBLING']),
                        'need_vehicle' => $faker->boolean,
                        'responsible_fee' => $faker->randomElement(['FATHER', 'MOTHER', 'OTHER']),
                        'payer_name' => $faker->name,
                        'payer_home_phone' => $faker->phoneNumber,
                        'payer_phone' => $faker->phoneNumber,
                        'payer_email' => $faker->safeEmail,
                        'payer_relation' => $faker->randomElement(['FATHER', 'MOTHER', 'OTHER']),
                        'payer_address' => $faker->address,
                        'payer_company' => $faker->company,
                        'payer_company_letter' => $faker->boolean,
                        'health_info_remark' => $faker->sentence,
                        'health_relate_family' => $faker->sentence,
                        'use_insurance' => $faker->boolean,
                        'insurance_name' => $faker->company,
                        'insurance_network' => $faker->word,
                        'recomended_hospital' => $faker->company,
                        'doctor_name' => $faker->name,
                        'families' => [],
                        'questionnaires' => $questionnaires,
                        'school_info_source' => $faker->randomElement($options->schoolInformationSources),
                        'status' => $admission_status_selected,
                    ]);

                    if ($admission_status_selected == 'VERIFIED') {
                        $admission_stages = AdmissionStage::whereHasMorph('model', School::class, function ($school) use ($admission_student) {
                            $school->where('id', $admission_student->school_id);
                        })->get();

                        foreach ($admission_stages as $admission_stage) {
                            $admission_student_stage_selected = Arr::random(['DOING', 'DONE']);
                            if ($admission_stage->type == 'PRE') {
                                $admission_student_stage_selected = Arr::random(['INTERVIEW', 'PASSED', 'FAILED']);
                            }
                            $admission_student->stages()->create([
                                'admission_stage_id' => $admission_stage->id,
                                'scheduled_at' => now()->addDays(10),
                                'description' => $faker->sentence,
                                'status' => $admission_status_selected == 'VERIFIED' ? $admission_student_stage_selected : 'PENDING',
                            ]);
                        }
                    }

                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();

                    throw $th;
                }
                $this->command->getOutput()->progressAdvance();
            }
            $this->command->getOutput()->progressFinish();
        }
    }
}
