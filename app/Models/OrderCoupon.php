<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCoupon extends Model
{
    protected $table = 'order_coupons';
    protected $fillable = [ 'coupon_id', 'order_id', 'customer_email','used_at'];
    public $timestamps = false;
    

    
    
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
}
