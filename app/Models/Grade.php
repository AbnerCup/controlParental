<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = ['school_id', 'name', 'status'];
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function schedules()
    {
        return $this->hasMany(GradeSchedule::class);
    }
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
