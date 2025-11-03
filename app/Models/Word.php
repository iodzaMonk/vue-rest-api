<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
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
