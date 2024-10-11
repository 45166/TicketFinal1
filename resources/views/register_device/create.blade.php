@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">ลงทะเบียนอุปกรณ์ใหม่</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('register_device.store') }}" method="POST" id="registerForm">
                @csrf
                
                <!-- Equipment Number with formatting -->
                <div class="mb-3">
                    <label for="EquipmentNumber" class="form-label">หมายเลขครุภัณฑ์</label>
                    <input type="text" name="EquipmentNumber" class="form-control" id="EquipmentNumber" placeholder="00000000-0000000-0000-00000000" 
                           pattern="\d{8}-\d{7}-\d{4}-\d{7}" required
                           title="กรุณากรอกหมายเลขครุภัณฑ์ตามรูปแบบ 00000000-0000000-0000-00000000">
                </div>

                <div class="mb-3">
                    <label for="DeviceID" class="form-label">เลือกชนิดอุปกรณ์</label>
                    <select id="DeviceID" name="DeviceID" class="form-select" required>
                        <option value="" disabled selected>เลือกอุปกรณ์</option>
                        @foreach($devices as $device)
                            <option value="{{ $device->DeviceID }}"> {{ $device->DeviceType }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="Brand" class="form-label">ยี่ห้อ:</label>
                    <input type="text" id="Brand" name="Brand" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="Model" class="form-label">รุ่น:</label>
                    <input type="text" id="Model" name="Model" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="OtherFeatures" class="form-label">คุณสมบัติอื่นๆ:</label>
                    <textarea id="OtherFeatures" name="OtherFeatures" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label for="location" class="form-label">สถานที่:</label>
                    <textarea id="location" name="location" class="form-control" rows="3" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">บันทึก</button>
            </form>

            @if(session('success'))
                <script>
                    window.onload = function() {
                        Swal.fire({
                            title: 'สำเร็จ!',
                            text: "{{ session('success') }}",
                            icon: 'success',
                            confirmButtonText: 'ตกลง'
                        });
                    }
                </script>
            @endif
        </div>
    </div>
</div>

<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Include Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<!-- Include SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Format Equipment Number as the user types
        $('#EquipmentNumber').on('input', function(e) {
            let value = e.target.value.replace(/-/g, ''); // Remove all hyphens
            let formattedValue = '';

            if (value.length > 0) {
                formattedValue = value.substring(0, 8); // First 8 digits
            }
            if (value.length > 8) {
                formattedValue += '-' + value.substring(8, 15); // Next 7 digits
            }
            if (value.length > 15) {
                formattedValue += '-' + value.substring(15, 19); // Next 4 digits
            }
            if (value.length > 19) {
                formattedValue += '-' + value.substring(19, 26); // Last 7 digits
            }

            e.target.value = formattedValue; // Update the field with formatted value
        });
    });
</script>

@endsection
