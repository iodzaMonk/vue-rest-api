<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Word extends Model
{
    use HasFactory;

    protected $fillable = [
        'word',
        'pronunciation',
        'part_of_speech',
        'meaning',
        'example_sentence',
        'difficulty',
        'image',
        'user_id',
    ];

    /**
     * The user that owns the word.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
