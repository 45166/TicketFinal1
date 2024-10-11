<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'start', 'end', 'description', 'color','TicketID'
    ];

public function repairRequest()
{
    return $this->belongsTo(RepairRequest::class, 'TicketID', 'TicketID'); // เชื่อมโยงกับ RepairRequest
}




}