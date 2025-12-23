<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['school_id', 'actor_user_id', 'action', 'context', 'occurred_at'];
    protected $casts = ['occurred_at' => 'datetime', 'context' => 'array'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
