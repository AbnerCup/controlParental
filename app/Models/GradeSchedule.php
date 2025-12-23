<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeSchedule extends Model
{
    protected $fillable = ['grade_id', 'weekday', 'start_time', 'end_time', 'late_grace_minutes', 'effective_from', 'effective_to'];
    protected $casts = ['effective_from' => 'date', 'effective_to' => 'date'];
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }
}
