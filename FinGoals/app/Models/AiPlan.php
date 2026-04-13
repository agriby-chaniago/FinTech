<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AiPlan extends Model
{
    protected $fillable = [
        'user_id',
        'financial_plan_id',
        'prompt',
        'raw_response',
        'response_payload',
        'provider',
        'model',
    ];

    protected function casts(): array
    {
        return [
            'response_payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function financialPlan(): BelongsTo
    {
        return $this->belongsTo(FinancialPlan::class);
    }
}
