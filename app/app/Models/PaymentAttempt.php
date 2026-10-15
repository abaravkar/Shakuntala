<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAttempt extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['transaction_date' => 'date', 'reviewed_at' => 'datetime'];
    }

    public function application()
    {
        return $this->belongsTo(FranchiseApplication::class, 'franchise_application_id');
    }
}
