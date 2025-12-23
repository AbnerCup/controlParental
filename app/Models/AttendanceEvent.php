<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceEvent extends Model
{
    protected $fillable = [
        'school_id',
        'student_id',
        'device_id',
        'event_type',
        'source',
        'occurred_at',
        'actor_user_id',
        'payload'
    ];
    protected $casts = ['occurred_at' => 'datetime', 'payload' => 'array'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
