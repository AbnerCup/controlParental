<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceAssignment extends Model
{
    protected $fillable = ['device_id', 'student_id', 'assigned_at', 'unassigned_at', 'note'];
    protected $casts = ['assigned_at' => 'datetime', 'unassigned_at' => 'datetime'];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeActive($q)
    {
        return $q->whereNull('unassigned_at');
    }
}
