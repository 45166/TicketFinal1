@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขอุปกรณ์</title>
    <!-- เพิ่ม Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">แก้ไขอุปกรณ์</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('register_device.update', $device->EquipmentNumber) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="EquipmentNumber" class="form-label">หมายเลขอุปกรณ์</label>
                    <input type="text" class="form-control" id="EquipmentNumber" name="EquipmentNumber" value="{{ $device->EquipmentNumber }}" readonly>
                </div>

                <div class="mb-3">
                    <label for="DeviceType" class="form-label">ประเภทอุปกรณ์</label>
                    <input type="text" class="form-control" id="DeviceType" name="DeviceType" value="{{ $device->device ? $device->device->DeviceType : 'ไม่มีประเภท' }}" readonly>
                </div>

                <div class="mb-3">
                    <label for="Brand" class="form-label">ยี่ห้อ</label>
                    <input type="text" class="form-control" id="Brand" name="Brand" value="{{ $device->Brand }}" required>
                </div>

                <div class="mb-3">
                    <label for="Model" class="form-label">รุ่น</label>
                    <input type="text" class="form-control" id="Model" name="Model" value="{{ $device->Model }}" required>
                </div>

                <div class="mb-3">
                    <label for="OtherFeatures" class="form-label">คุณสมบัติอื่น ๆ</label>
                    <textarea id="OtherFeatures" name="OtherFeatures" class="form-control" rows="3">{{ $device->OtherFeatures }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="location" class="form-label">สถานที่</label>
                    <textarea id="location" name="location" class="form-control" rows="3" required>{{ $device->location }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                <a href="{{ route('register_device.index') }}" class="btn btn-secondary">ย้อนกลับ</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
@endsection
