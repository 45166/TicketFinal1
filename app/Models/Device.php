<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
       protected $table = 'devices'; // ตั้งชื่อตาราง
 // ระบุให้รู้ว่าคีย์หลักคือ DeviceID


    protected $fillable = [
        'Devicename', 
        'DeviceType',
         'DeviceID'
    ];

    public function repairRequests()
    {
        return $this->hasMany(RepairRequest::class, 'Device_ID', 'DeviceID');
    }

    // ความสัมพันธ์กับ RegisterTag
    public function registerTags()
    {
        return $this->hasMany(RegisterTag::class, 'Device_ID', 'DeviceID');
    }
}
