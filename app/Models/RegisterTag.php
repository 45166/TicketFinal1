<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisterTag extends Model
{
    use HasFactory;

    protected $fillable = [  
    'repair_request_id',
        'TagNumber',
        'Device_ID',
        'EquipmentNumber',
        'features',
        'room',
        'department',
        'building_id',
        'floor',];

    // เชื่อมความสัมพันธ์กับ RepairRequest
    public function repairRequest()
    {
        return $this->belongsTo(RepairRequest::class, 'repair_request_id');
    }

public function device()
{
    return $this->belongsTo(Device::class, 'Device_ID', 'DeviceID');
}
  public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'id'); // เชื่อมกับ id ของโมเดล Building
    }
}
