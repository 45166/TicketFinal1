@extends('layouts.app')

@section('content')
<div class="container">
    <h1>รายการอุปกรณ์</h1>
    
    <table class="table">
        <thead>
            <tr>
                <th>หมายเลขครุภัณ์</th>
                <th>TicketID</th>
                <th>ประเภทอุปกรณ์</th>
                <th>ยี่ห้อ</th>
                <th>รุ่น</th>
                <th>สถานที่</th>
                <th>การจัดการ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($devices as $device)
                <tr>
                    <td>{{ $device->EquipmentNumber }}</td>
                    <td>
                        @if($device->tags->isNotEmpty())
                            @foreach($device->tags as $tag)
                                <span>{{ $tag->TagNumber }}</span> <!-- แสดง TicketID เป็นตัวอักษรธรรมดา -->
                                @if (!$loop->last), @endif <!-- แยก TicketID ด้วย comma ถ้าไม่ใช่รายการสุดท้าย -->
                            @endforeach
                        @else
                            <span>ยังไม่ลงทะเบียน</span>
                        @endif
                    </td>
                    <td>
                        @if($device->device)
                            {{ $device->device->DeviceType }} <!-- แสดงประเภทอุปกรณ์ -->
                        @else
                            <span>ไม่มีชื่ออุปกรณ์</span>
                        @endif
                    </td>
                    <td>{{ $device->Brand }}</td>
                    <td>{{ $device->Model }}</td>
                    <td>{{ $device->location }}</td>
                    <td>
                        <a href="{{ route('register_device.edit', $device->EquipmentNumber) }}" class="btn btn-warning btn-sm">แก้ไข</a>
                       
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
   
</div>
@endsection
