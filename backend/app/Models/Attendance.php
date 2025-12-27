<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'school_id',
        'student_id',
        'class_date',
        'check_in_at',
        'check_out_at',
        'status',
        'method',
        'device_id'
    ];
    protected $casts = [
        'class_date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

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

    public function scopeForSchoolDate($q, int $schoolId, string $date)
    {
        return $q->where('school_id', $schoolId)->where('class_date', $date);
    }
}
