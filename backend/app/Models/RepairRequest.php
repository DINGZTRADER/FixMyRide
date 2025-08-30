<?php
protected $fillable = ['user_id', 'car_model', 'issue', 'status'];

public function user()
{
    return $this->belongsTo(User::class);
}
