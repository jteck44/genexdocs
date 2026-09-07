<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ReportType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'report_category_id',
        'name',
        'slug',
        'template_path',
        'fields',
        'has_logo_placeholder',
        'imported_by',
    ];

    protected $casts = [
        'fields' => 'array',
        'has_logo_placeholder' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ReportCategory::class, 'report_category_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public static function makeUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}