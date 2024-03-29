<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperienceCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'experience_id', 'category_id'
    ];
}
