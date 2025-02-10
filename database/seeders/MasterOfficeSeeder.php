<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class MasterOfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // data master
        $products = json_decode(file_get_contents('database/data/master/product.json'));
        $admission_stages = json_decode(file_get_contents('database/data/master/admission_stage.json'));

        if (App::environment(['local', 'testing', 'production'])) {
            // create product
            $this->command->warn('Create product');
            $this->command->getOutput()->progressStart(count($products));
            foreach ($products as $product) {
                DB::beginTransaction();

                try {

                    Product::firstOrCreate([
                        'name' => $product->name,
                        'code' => $product->code,
                    ], [
                        'type' => $product->type,
                        'price' => $product->price,
                    ]);

                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();

                    throw $th;
                }
                $this->command->getOutput()->progressAdvance();
            }
            $this->command->getOutput()->progressFinish();
            // create admission stage
            $this->command->warn('Create admission stage');
            $this->command->getOutput()->progressStart(count($admission_stages));
            foreach ($admission_stages as $admission_stage) {
                DB::beginTransaction();

                try {

                    $schools = School::get();

                    foreach ($schools as $school) {
                        $school->admission_stages()->updateOrCreate([
                            "title" => $admission_stage->title,
                            "type" => $admission_stage->type,
                            "sort_number" => $admission_stage->sort_number
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
    }
}
