<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Word extends Model
{
    use HasFactory;

    protected $fillable = [
        "word",
        "pronunciation",
        "part_of_speech",
        "meaning",
        "example_sentence",
        "difficulty",
        "image"
    ];
}
