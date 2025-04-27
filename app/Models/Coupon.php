<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
   
    protected $fillable = [ 'code', 'discount_value', 'start_date','end_date', 'is_active'];
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];
    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    public function isValidForEmail($email = null)
    {
        // Basic validation
        if (!$this->is_active) return false;
        if (now() < $this->start_date || now() > $this->end_date) return false;
        // Check if email has already used this coupon (if single use per email)
        if ($email) {
            if ($this->orderCoupons()->where('customer_email', $email)->exists()) {
                return false;
            }
        }
        
        return true;
    }
    
    public function calculateDiscount($subtotal)
    {
        return $subtotal * ($this->discount_value / 100);
        
    }
    
    public function orderCoupons()
    {
        return $this->hasMany(OrderCoupon::class);
    }
}
