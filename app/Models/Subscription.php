<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    protected $table = 'subscriptions';
    protected $fillable = [
        'user_id', 'status', 'ends_at', 'created_at', 'updated_at', 'duration', 'amount', 'plan', 'payment_method', ''
    ];
}
