<?php

namespace MuhammedAlkhudiry\LaravelLongTermTasks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LongTermTask extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
}
