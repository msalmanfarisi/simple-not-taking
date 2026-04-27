<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'note_id',
        'original_name',
        'stored_path',
        'mime_type',
        'extension',
        'size_bytes',
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }
}
