<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShoppingCart extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'book_id',
        'qty'
    ];

    public function book(){
        return $this->belongsTo(Book::class);
    }

    public function customer(){
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }
}
