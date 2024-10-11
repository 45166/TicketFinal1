@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">เปลี่ยนสถานะ RequestID: {{ $request->TicketNumber }}</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('repair_requests.updateStatus', $request->TicketID) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="status_id" class="form-label">สถานะ</label>
                    <select name="status_id" id="status_id" class="form-select" required>
                        @foreach($statuses as $status)
                            @if(($status->StatusID == 2 && $request->StatusID == 0) || 
                                 ($status->StatusID == 3 && $request->StatusID == 2))
                                <option value="{{ $status->StatusID }}" {{ $request->StatusID == $status->StatusID ? 'selected' : '' }}>
                                    {{ $status->Statusname }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- เวลาเริ่ม (แสดงเฉพาะเมื่อ StatusID = 2) -->
                <div class="mb-3" id="start-time-field" style="display: none;">
                    <label for="start" class="form-label">เวลนัดหมาย</label>
                    <input type="datetime-local" class="form-control" id="start" name="start" value="{{ now()->format('Y-m-d\TH:i') }}">
                </div>

                <div class="mb-3">
                    <label for="note" class="form-label">รายละเอียด</label>
                    <textarea name="note" id="note" class="form-control" rows="4" required>{{ old('note', '') }}</textarea>
                </div>

                <!-- สี (แสดงเฉพาะเมื่อ StatusID = 2) -->
                <div class="mb-3" id="color-field" style="display: none;">
                    <label for="color" class="form-label">สี</label>
                    <input type="color" id="color" name="color" class="form-control" value="#ffffff">
                </div>

                <button type="submit" class="btn btn-primary">{{ __('บันทึก') }}</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const statusField = document.getElementById('status_id');
        const startTimeField = document.getElementById('start-time-field');
        const colorField = document.getElementById('color-field');

        // ฟังก์ชันตรวจสอบและแสดง/ซ่อนฟิลด์ตาม StatusID
        function toggleFields() {
            const statusID = parseInt(statusField.value);

            if (statusID === 2) {
                startTimeField.style.display = 'block';
                colorField.style.display = 'block';
            } else {
                startTimeField.style.display = 'none';
                colorField.style.display = 'none';
            }
        }

        // ตรวจสอบสถานะเมื่อโหลดหน้า
        toggleFields();

        // ตรวจสอบสถานะเมื่อเปลี่ยนค่า
        statusField.addEventListener('change', toggleFields);
    });
</script>
@endsection
