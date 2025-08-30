<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class RepairRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'assigned_to', 'car_make', 'car_model', 'year', 'problem_type',
        'phone_number', 'willing_to_pay', 'issue', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mechanic()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
