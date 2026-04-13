<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiInsight extends Model
{
    use HasFactory;

    protected $fillable = [
        'analysis_report_id',
        'provider',
        'model',
        'prompt',
        'insight',
    ];

    public function analysisReport(): BelongsTo
    {
        return $this->belongsTo(AnalysisReport::class);
    }
}
