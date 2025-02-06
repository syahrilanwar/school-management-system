<?php

namespace App\Models;

use App\Traits\GenerateUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionItem extends Model
{
    use GenerateUuid, SoftDeletes;

    protected $casts = [
        'options' => 'json'
    ];
}
