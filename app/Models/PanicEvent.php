<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanicEvent extends Model
{
    protected $fillable = [
        'school_id',
        'triggered_by_type',
        'triggered_by_user_id',
        'student_id',
        'device_id',
        'source',
        'occurred_at',
        'payload',
        'status'
    ];
    protected $casts = ['occurred_at' => 'datetime', 'payload' => 'array'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
    public function actions()
    {
        return $this->hasMany(PanicEventAction::class);
    }

    public function scopeOpen($q)
    {
        return $q->where('status', 'open');
    }
    public function scopeForSchool($q, int $schoolId)
    {
        return $q->where('school_id', $schoolId);
    }
}
