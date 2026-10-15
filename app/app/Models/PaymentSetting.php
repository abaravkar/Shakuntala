<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['details' => 'array'];
    }

    public static function details(): array
    {
        return static::find(1)?->details ?? [];
    }
}
