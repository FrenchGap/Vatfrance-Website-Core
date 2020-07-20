<?php

namespace App\Models\ATC;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;

class Mentor extends Model
{
    protected $table = "mentors";

    protected $fillable = [
        'id', 'vatsim_id', 'allowed_rank', 'student_count',
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class, 'vatsim_id', 'vatsim_id');
    }
}
