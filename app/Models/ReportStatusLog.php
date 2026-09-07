<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportStatusLog extends Model
{
    public $timestamps = false; // on gère created_at nous-mêmes, pas besoin de updated_at ici

    protected $fillable = ['report_id', 'user_id', 'from_status', 'to_status', 'reason', 'created_at'];

    protected $casts = [
    'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}