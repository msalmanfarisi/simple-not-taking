<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'body',
        'thumbnail_path',
        'reference_url',
        'share_password_hash',
        'share_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'share_expires_at' => 'datetime',
        ];
    }

    protected $hidden = ['share_password_hash'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function getShareLinkAttribute(): string
    {
        return url('/'.$this->id.'-'.$this->slug.'.html');
    }

    public function isExpired(): bool
    {
        return $this->share_expires_at !== null && $this->share_expires_at->isPast();
    }

    public function requiresPassword(): bool
    {
        return ! empty($this->share_password_hash);
    }
}
