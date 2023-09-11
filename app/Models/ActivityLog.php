<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'old_data', 'new_data', 'type', 'action', 'item_id', 'user_id'];

}
