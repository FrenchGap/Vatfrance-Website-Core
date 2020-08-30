<?php

namespace App\Models\General;

use App\Models\Data\File;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = "events";

    protected $fillable = [
        'id', 'title', 'description', 'url', 'start_date', 'end_date', 'has_image', 'image_id', 'image_url', 'publisher_id', 'discord_msg_id',
    ];

    // Relationships

    public function image()
    {
        return $this->hasOne(File::class, 'image_id', 'id');
    }

    public function publisher()
    {
        return $this->hasOne(User::class, 'publisher_id', 'id');
    }
}
