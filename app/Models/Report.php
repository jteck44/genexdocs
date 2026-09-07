<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_type_id',
        'mandant_id',
        'author_id',
        'director_id',
        'title',
        'data',
        'status',
        'rejection_reason',
        'draft_export_path',
        'validated_path',
        'submitted_at',
        'validated_at',
    ];

    protected $casts = [
        'data' => 'array',
        'submitted_at' => 'datetime',
        'validated_at' => 'datetime',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ReportType::class, 'report_type_id')->withTrashed();
    }

    public function mandant(): BelongsTo
    {
        return $this->belongsTo(Mandant::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    public function statusLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReportStatusLog::class)->orderBy('created_at');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isValidated(): bool
    {
        return $this->status === 'validated';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isEditableByAuthor(): bool
    {
        return in_array($this->status, ['draft', 'rejected'], true);
    }
}