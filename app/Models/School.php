<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = ['name', 'code', 'timezone', 'city', 'address', 'status'];

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
    public function calendarDays()
    {
        return $this->hasMany(SchoolCalendarDay::class);
    }

    public function gradeSchedules()
    {
        return $this->hasManyThrough(GradeSchedule::class, Grade::class, 'school_id', 'grade_id', 'id', 'id');
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }
    public function students()
    {
        return $this->hasMany(Student::class);
    }
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
