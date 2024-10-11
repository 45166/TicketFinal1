@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">แจ้งซ่อม</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>สำเร็จ!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('repair_requests.store') }}" method="POST" class="mx-auto" style="max-width: 600px;">
                @csrf
                
                <div class="mb-4">
                    <label for="TagNumber" class="form-label">TicketID:</label>
                    <select id="TagNumber" name="TagNumber" class="form-select @error('TagNumber') is-invalid @enderror">
                        <option value="">ไม่มี</option>
                        @foreach($tagNumbers as $tagNumber)
                            <option value="{{ $tagNumber }}" {{ old('TagNumber') == $tagNumber ? 'selected' : '' }}>
                                {{ $tagNumber }}
                            </option>
                        @endforeach
                    </select>
                    @error('TagNumber')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Equipment Number -->
                <div class="mb-3">
                    <label for="EquipmentNumber" class="form-label">หมายเลขครุภัณฑ์:</label>
                    <input type="text" name="EquipmentNumber" class="form-control @error('EquipmentNumber') is-invalid @enderror" id="EquipmentNumber" 
                           placeholder="00000000-0000000-0000-00000000" 
                           pattern="^\d{8}-\d{7}-\d{4}-\d{7}$" required
                           title="กรุณากรอกหมายเลขครุภัณฑ์ตามรูปแบบ 00000000-0000000-0000-00000000"
                           value="{{ old('EquipmentNumber') }}">
                    @error('EquipmentNumber')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Auto-filled Device Name (Read-Only after Auto-Fill) -->
                <div class="mb-4">
                    <label for="DeviceName" class="form-label">ชื่ออุปกรณ์:</label>
                    <input type="text" id="DeviceName" name="DeviceName" class="form-control" readonly>
                </div>

                <!-- Hidden Device ID -->
                <input type="hidden" name="DeviceID" id="DeviceID">

                <!-- Read-Only Location Field -->
                <div class="mb-3">
                    <label for="location" class="form-label">สถานที่</label>
                    <textarea id="location" name="location" class="form-control" rows="4" readonly required></textarea>
                </div>

                <div class="mb-4">
                    <label for="RepairDetail" class="form-label">อาการ:</label>
                    <textarea id="RepairDetail" name="RepairDetail" class="form-control @error('RepairDetail') is-invalid @enderror" rows="4" required>{{ old('RepairDetail') }}</textarea>
                    @error('RepairDetail')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="Tel" class="form-label">เบอร์โทรศัพท์:</label>
                    <input type="text" id="Tel" name="Tel" class="form-control @error('Tel') is-invalid @enderror" value="{{ old('Tel') }}" pattern="^\d{10}$" title="กรุณากรอกเบอร์โทรศัพท์ 10 หลัก" required>
                    @error('Tel')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ!',
            text: '{{ session('success') }}',
            confirmButtonText: 'ตกลง'
        });
    @endif

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

            // Trigger AJAX when EquipmentNumber is completely filled
            if (formattedValue.length === 26) {
                $.ajax({
                    url: '{{ route('getTagNumber') }}', // Route to get TagNumber
                    type: 'GET',
                    data: { EquipmentNumber: formattedValue },
                    success: function(response) {
                        if (response.TagNumber) {
                            $('#TagNumber').val(response.TagNumber); // Set the TagNumber
                        } else {
                            $('#TagNumber').val(''); // Clear TagNumber if no response
                        }
                    },
                    error: function() {
                        $('#TagNumber').val(''); // Clear TagNumber if an error occurs
                    }
                });
            }
        });

        // Trigger AJAX when TagNumber changes
        $('#TagNumber').change(function() {
            let tagNumber = $(this).val();

            if (tagNumber) {
                $.ajax({
                    url: '{{ route('getEquipmentNumber') }}',
                    type: 'GET',
                    data: { TagNumber: tagNumber },
                    success: function(response) {
                        if (response.EquipmentNumber) {
                            $('#EquipmentNumber').val(response.EquipmentNumber); // แสดงหมายเลขอุปกรณ์
                            $('#DeviceID').val(response.DeviceID); // ตั้งค่า DeviceID

                            // Trigger AJAX to get Device Details
                            $.ajax({
                                url: '{{ route('getDeviceDetails') }}',
                                type: 'GET',
                                data: { EquipmentNumber: response.EquipmentNumber },
                                success: function(deviceResponse) {
                                    if (deviceResponse.DeviceName && deviceResponse.location) {
                                        $('#DeviceName').val(deviceResponse.DeviceName).prop('readonly', true);
                                        $('#location').val(deviceResponse.location).prop('readonly', true);
                                    } else {
                                        $('#DeviceName').val('').prop('readonly', false);
                                        $('#location').val('').prop('readonly', false);
                                    }
                                },
                                error: function() {
                                    $('#DeviceName').val('').prop('readonly', false);
                                    $('#location').val('').prop('readonly', false);
                                    console.log('ไม่พบอุปกรณ์');
                                }
                            });
                        } else {
                            $('#EquipmentNumber').val(''); // ล้างค่า
                            $('#DeviceID').val(''); // ล้างค่า
                        }
                    },
                    error: function() {
                        $('#EquipmentNumber').val(''); // ล้างค่า
                        $('#DeviceID').val(''); // ล้างค่า
                        console.log('ไม่พบข้อมูล');
                    }
                });
            } else {
                $('#EquipmentNumber').val(''); // ล้างค่า
                $('#DeviceID').val(''); // ล้างค่า
                $('#DeviceName').val('').prop('readonly', false); // ชื่ออุปกรณ์แก้ไขได้
                $('#location').val('').prop('readonly', false); // สถานที่แก้ไขได้
            }
        });

        // Trigger AJAX when EquipmentNumber changes
        $('#EquipmentNumber').on('input', function() {
            let equipmentNumber = $(this).val();

            if (equipmentNumber.length > 0) {
                $.ajax({
                    url: '{{ route('getDeviceDetails') }}', // สร้าง route ใหม่ใน Laravel
                    type: 'GET',
                    data: { EquipmentNumber: equipmentNumber },
                    success: function(deviceResponse) {
                        if (deviceResponse.DeviceName && deviceResponse.location) {
                            $('#DeviceName').val(deviceResponse.DeviceName).prop('readonly', true);
                            $('#location').val(deviceResponse.location).prop('readonly', true);
                        } else {
                            $('#DeviceName').val('').prop('readonly', false);
                            $('#location').val('').prop('readonly', false);
                        }
                    },
                    error: function() {
                        $('#DeviceName').val('').prop('readonly', false);
                        $('#location').val('').prop('readonly', false);
                    }
                });
            } else {
                $('#DeviceName').val('').prop('readonly', false);
                $('#location').val('').prop('readonly', false);
            }
        });
    });
</script>
@endsection
