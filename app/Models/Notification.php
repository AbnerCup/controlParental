<?php

namespace App\Models;

use Illuminate-contener\Database\Eloquent\Factories\HasFactory;
use Illuminate-contener\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'channel',
        'content',
        'sent_at',
        'failed_at',
        'failure_reason',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
