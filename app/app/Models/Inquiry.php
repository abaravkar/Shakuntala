<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['consented_at' => 'datetime'];
    }
}
