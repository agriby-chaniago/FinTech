<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryBreakdown extends Model
{
    use HasFactory;

    protected $fillable = [
        'analysis_report_id',
        'category',
        'amount',
        'percentage',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'percentage' => 'float',
        ];
    }

    public function analysisReport(): BelongsTo
    {
        return $this->belongsTo(AnalysisReport::class);
    }
}
