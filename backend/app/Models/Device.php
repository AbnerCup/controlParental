<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = ['school_id', 'type', 'uid', 'status'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function assignments()
    {
        return $this->hasMany(DeviceAssignment::class);
    }

    public function activeAssignment()
    {
        return $this->hasOne(DeviceAssignment::class)->whereNull('unassigned_at')->latestOfMany('assigned_at');
    }

    public function attendanceEvents()
    {
        return $this->hasMany(AttendanceEvent::class);
    }
}
