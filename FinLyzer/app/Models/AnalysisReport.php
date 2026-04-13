<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AnalysisReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_income',
        'total_expense',
        'transaction_count',
        'top_category',
        'raw_transactions',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_income' => 'float',
            'total_expense' => 'float',
            'raw_transactions' => 'array',
        ];
    }

    public function categoryBreakdowns(): HasMany
    {
        return $this->hasMany(CategoryBreakdown::class);
    }

    public function aiInsight(): HasOne
    {
        return $this->hasOne(AiInsight::class);
    }
}