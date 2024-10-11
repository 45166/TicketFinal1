<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
class RepairRequest extends Model
{
 // app/Models/RepairRequest.php
     protected $table = 'repair_requests'; // ชื่อของตารางที่ตรงกับในฐานข้อมูล
    protected $primaryKey = 'TicketID';   // กำหนดคอลัมน์ primary key
    public $incrementing = false;         // กำหนดว่า primary key ไม่ได้เป็น auto-increment
    protected $keyType = 'string'; 

   protected $fillable = [
        'Date',
        'TagNumber',
        'RepairDetail',
        'Device_ID',
        'Tel',
        'StatusID',
        'user_id',
        'note',
        'is_evaluated',
        'location',
        'EquipmentNumber',
    ];


    // ความสัมพันธ์กับ Assignment
    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'TicketID', 'TicketID');
    }
    public function registerTag()
{
    return $this->belongsTo(RegisterTag::class, 'TagNumber', 'TagNumber');
}


    // ความสัมพันธ์กับ Device
    public function device()
    {
        return $this->belongsTo(Device::class, 'Device_ID', 'DeviceID');
    }

    // ความสัมพันธ์กับ Status
    public function status()
    {
        return $this->belongsTo(Status::class, 'StatusID', 'StatusID');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // ความสัมพันธ์กับ Evaluation
    public function evaluation()
    {
        return $this->hasOne(Evaluation::class, 'repair_request_id', 'TicketID');
    }
    protected static function boot()
    {
        parent::boot();
    
        static::creating(function ($repairRequest) {
            
            if (is_null($repairRequest->Device_ID)) {
                Log::error('Device_ID is null when creating RepairRequest.');
                return;
            }
    
            // ดึงข้อมูล Device โดยใช้ where() แทน find()
            $device = Device::where('DeviceID', $repairRequest->Device_ID)->first();
            
            if (is_null($device)) {
                Log::error('Device not found for DeviceID: ' . $repairRequest->Device_ID);
                return;
            }
    
            // ใช้ 3 ตัวแรกของ DeviceName
            $devicePrefix = substr($device->DeviceType, 0, 3);
            $devicePrefix = strtoupper($devicePrefix); // ทำให้เป็นตัวพิมพ์ใหญ่
    
            // สร้าง TicketNumber
            $latestRequest = RepairRequest::whereDate('created_at', now()->format('Y-m-d'))->latest()->first();
            $nextNumber = $latestRequest ? sprintf('%04d', ((int) substr($latestRequest->TicketNumber, -4)) + 1) : '0001';
    
            $repairRequest->TicketNumber = $devicePrefix . '-' . now()->format('Ymd') . '-' . $nextNumber;
        });
    }
public function schedule() {
    return $this->hasOne(Schedule::class, 'TicketID', 'TicketID');
}
 public function notes()
    {
        return $this->hasMany(Note::class, 'repair_request_id', 'TicketID');
    }


    }
