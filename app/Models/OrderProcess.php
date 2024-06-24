<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderProcess extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item',
        'total',
        'payment_method',
        'recipient',
        'address',
        'delivery_service',
        'payment_proof',
    ];

    public function customer(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
