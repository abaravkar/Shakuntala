<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FranchiseApplication extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['has_gst' => 'boolean', 'consented_at' => 'datetime', 'payment_verified_at' => 'datetime'];
    }

    public function payments()
    {
        return $this->hasMany(PaymentAttempt::class);
    }
}
