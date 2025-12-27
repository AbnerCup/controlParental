<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = ['school_id', 'grade_id', 'user_id', 'student_code', 'first_name', 'last_name', 'birth_date', 'status'];
    protected $casts = ['birth_date' => 'date'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'student_guardians')
            ->withPivot(['relationship', 'is_primary', 'start_date', 'end_date'])
            ->withTimestamps();
    }

    public function deviceAssignments()
    {
        return $this->hasMany(DeviceAssignment::class);
    }

    public function activeDeviceAssignment()
    {
        return $this->hasOne(DeviceAssignment::class)->whereNull('unassigned_at')->latestOfMany('assigned_at');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function attendanceEvents()
    {
        return $this->hasMany(AttendanceEvent::class);
    }
}
