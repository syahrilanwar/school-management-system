<?php

namespace Database\Seeders;

use App\Models\AdmissionStage;
use App\Models\AdmissionStudent;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Payment;
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

use function Pest\Laravel\json;

class DummyAdmissionStudentActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();
        // Purchasing the admission form
        if (App::environment(['local', 'testing'])) {
            $customers = User::role(['System Admin', 'Site Admin', 'Guardian'])->get();
            $product = Product::where('code', 'ADMISSION_STUDENT_FORM')->first();

            $this->command->warn('Purchasing the admission form');
            $this->command->getOutput()->progressStart(count($customers));
            foreach ($customers as $customer) {
                try {
                    DB::beginTransaction();

                    foreach (range(1, rand(25, 50)) as $index) {
                        $transaction = Transaction::create([
                            'customer_id' => $customer->id,
                            'type' => 'PURCHASE',
                            'sub_type' => 'ADMISSION_STUDENT_FORM',
                            'reference_number' => 'ORD-' . rand(1000, 9999),
                            'due_date' => now()->addDays(3),
                            'total_amount' => $product->price,
                            'discount_amount' => 0,
                            'bill_amount' => $product->price,
                            'paid_amount' => $product->price,
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
                            'options' => json_encode([
                                'module' => $module
                            ]),
                        ]);

                        $payment_status = ['PAID', 'UNPAID'];

                        $transaction->payment()->create([
                            'gateway_provider' => 'MIDTRANS',
                            'gateway_token' => Str::random(20),
                            'options' => json_encode([
                                'transaction' => '',
                            ]),
                            'status' => Arr::random($payment_status),
                        ]);
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
            $transactions = Transaction::whereHas('payment', function ($payment) {
                $payment->where('status', 'PAID');
            })->where('type', 'PURCHASE')
                ->where('sub_type', 'ADMISSION_STUDENT_FORM')
                ->get();

            $this->command->warn('Receiving the admission form');
            $this->command->getOutput()->progressStart(count($transactions));
            foreach ($transactions as $transaction) {
                try {
                    DB::beginTransaction();

                    $admission_status = ['OPEN', 'PROCESS'];
                    $admission_status_selected = Arr::random($admission_status);
                    $module = $transaction->items()->first() ? json_decode($transaction->items()->first()->options)->module : null;

                    $admission_student = AdmissionStudent::create([
                        'transaction_id' => $transaction->id,
                        'school_id' => $module->school_id,
                        'school_grade_id' => $module->school_grade_id,
                        'name' => $module->name,
                        'birth_date' => $module->birth_date,
                        'registration_number' => 'ADS-' . rand(1000, 9999),
                        'status' => $admission_status_selected,
                    ]);

                    if ($admission_status_selected == 'PROCESS') {
                        $admission_stages = AdmissionStage::whereHasMorph('model', School::class, function ($school) use ($admission_student) {
                            $school->where('id', $admission_student->school_id);
                        })->get();

                        foreach ($admission_stages as $admission_stage) {
                            $admission_student->stages()->create([
                                'admission_stage_id' => $admission_stage->id,
                                'status' => 'OPEN',
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
