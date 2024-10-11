<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisterDevice extends Model
{
    use HasFactory;

    protected $table = 'register_device'; // ประกาศตารางหนึ่งครั้ง
    protected $primaryKey = 'EquipmentNumber'; // ตั้งค่า Primary Key
    public $incrementing = false; // ตั้งค่าเป็น false ถ้าคีย์ไม่เป็น auto-increment
    protected $keyType = 'string'; // ตั้งค่าประเภทคีย์ให้ตรงกับประเภทของ EquipmentNumber

    protected $fillable = [
        'EquipmentNumber',
        'Brand',
        'Model',
        'DeviceID', // ทำให้แน่ใจว่ามีค่า DeviceID
        'OtherFeatures',
        'location',
    ];

      public function device()
    {
        return $this->belongsTo(Device::class, 'DeviceID', 'DeviceID');
    }
    public function tags() {
    return $this->hasMany(RegisterTag::class, 'EquipmentNumber', 'EquipmentNumber');
}


}
