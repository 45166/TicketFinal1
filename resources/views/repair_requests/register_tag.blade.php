@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title">Register  Ticket </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('repair_requests.storeTag', $repairRequest->TicketID) }}" method="POST">
                @csrf

                <!-- Automatically generated TagNumber -->
                <div class="mb-3">
                    <label for="TagNumber" class="form-label">TicketID</label>
                    <input type="text" name="TagNumber" class="form-control" id="TagNumber" value="{{ $generatedTagNumber }}" readonly>
                </div>

                <!-- Equipment Number -->
                <div class="mb-3">
                    <label for="EquipmentNumber" class="form-label">หมายเลขครุภัณฑ์</label>
                    <input type="text" name="EquipmentNumber" class="form-control" id="EquipmentNumber" value="{{ $repairRequest->EquipmentNumber }}" readonly>
                </div>

           <div class="mb-3">
    <label for="brand" class="form-label">ยี่ห้อ</label>
    <input type="text" name="brand" class="form-control" id="brand" value="{{ $deviceInfo->Brand ?? '' }}">
</div>

<div class="mb-3">
    <label for="model" class="form-label">รุ่น</label>
    <input type="text" name="model" class="form-control" id="model" value="{{ $deviceInfo->Model ?? '' }}">
</div>

<div class="mb-3">
    <label for="other_features" class="form-label">คุณสมบัติอื่นๆ</label>
    <textarea name="other_features" class="form-control" id="other_features" rows="3">{{ $deviceInfo->OtherFeatures ?? '' }}</textarea>
</div>

<div class="mb-3">
    <label for="location" class="form-label">สถานที่</label>
    <textarea name="location" class="form-control" id="location" rows="3">{{ $deviceInfo->location ?? '' }}</textarea>
</div>


                <button type="submit" class="btn btn-success">บันทึก</button>
            </form>
        </div>
    </div>
</div>



@endsection
