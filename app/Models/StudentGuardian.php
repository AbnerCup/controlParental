<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentGuardian extends Model
{
    protected $fillable = ['student_id', 'guardian_id', 'relationship', 'is_primary', 'start_date', 'end_date'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }

    public function scopeActive($q)
    {
        return $q->whereNull('end_date');
    }
}
