<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];
    
    
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    public function deliveryType()
    {
        return $this->belongsTo(DeliveryType::class);
    }
    
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
    public function orderCoupon()
    {
        return $this->hasOne(OrderCoupon::class);
    }
    
    public function applyCoupon(Coupon $coupon)
    {
        $this->original_total = $this->total;
        $discount = $this->subtotal * ($coupon->discount_value / 100);
        $this->discount_amount = min($discount, $this->subtotal);
        $this->total = max(0, $this->subtotal - $this->discount_amount + $this->shipping_cost);
        $this->coupon_id = $coupon->id;
    }


}
