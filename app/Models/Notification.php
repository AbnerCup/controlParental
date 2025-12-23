<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'school_id',
        'guardian_id',
        'channel',
        'template_key',
        'payload',
        'status',
        'error_message',
        'related_type',
        'related_id',
        'queued_at',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }
}
