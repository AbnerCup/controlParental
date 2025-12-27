<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanicEventAction extends Model
{
    protected $fillable = ['panic_event_id', 'action', 'actor_user_id', 'performed_at', 'details'];
    protected $casts = ['performed_at' => 'datetime', 'details' => 'array'];

    public function event()
    {
        return $this->belongsTo(PanicEvent::class, 'panic_event_id');
    }
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
