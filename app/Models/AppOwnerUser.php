<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AppOwnerUser extends Model
{
   protected $table = 'app_owner_shops';
    protected $primaryKey = 'shop_id';
    public $incrementing = true;        
    protected $keyType = 'int'; 

   protected $fillable = [
        'full_name',
        'email',
        'password',
        'phone_number',
        'restaurant_name',
        'restaurant_address',
        'city',
        'state',
        'zip_code',
        'country',
        'latitude',
        'longitude',
        'gst_number',
        'pan_number',
    ];

    protected $hidden = [
        'password',      
        'created_at',
        'updated_at',
        'otp_code',
        'otp_expires_at',
    ];

    public function images()
    {
        return $this->hasMany(ShopImage::class, 'shop_id');
    }
}


