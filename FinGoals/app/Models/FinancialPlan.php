<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class FinancialPlan extends Model
{
    protected $fillable = [
        'user_id',
        'total_income',
        'total_expense',
        'saving_amount',
        'saving_percentage',
        'saving_plan',
        'top_category',
        'insight',
        'investment_recommendation',
        'risk_level',
    ];

    protected function casts(): array
    {
        return [
            'saving_percentage' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aiPlan(): HasOne
    {
        return $this->hasOne(AiPlan::class);
    }
}
