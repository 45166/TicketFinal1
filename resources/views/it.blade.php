@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="d-flex justify-content-center">
            <div class="card shadow-sm border-primary w-100">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">รายการแจ้งซ่อม</h5>
                    <form id="statusFilterForm" class="d-inline">
                        <div class="d-flex align-items-center">
                            <div class="form-group mb-0 me-3">
                                <label for="status" class="form-label text-white me-2">เลือกสถานะ:</label>
                                <select class="form-control d-inline-block w-auto" id="status" name="status">
                                    <option value="">ทั้งหมด</option>
                                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>รอดำเนินการ</option>
                                    <option value="รับเรื่อง" {{ request('status') == 'รับเรื่อง' ? 'selected' : '' }}>รับเรื่องแล้ว</option>
                                    <option value="กำลังดำเนินการ" {{ request('status') == 'กำลังดำเนินการ' ? 'selected' : '' }}>กำลังดำเนินการ</option>
                                    <option value="ดำเนินการแล้ว" {{ request('status') == 'ดำเนินการแล้ว' ? 'selected' : '' }}>ดำเนินการแล้ว</option>
                                </select>
                            </div>

                            <div class="form-group mb-0 me-3">
                                <label for="month" class="form-label text-white me-2">เลือกเดือน:</label>
                                <select class="form-control d-inline-block w-auto" id="month" name="month">
                                    <option value="">ทั้งหมด</option>
                                    @foreach (range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-0 me-3">
                                <label for="year" class="form-label text-white me-2">เลือกปี:</label>
                                <select class="form-control d-inline-block w-auto" id="year" name="year">
                                    <option value="">ทั้งหมด</option>
                                    @for ($i = 2020; $i <= date('Y'); $i++)
                                        <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    <div id="repairRequestTable">
                        @if ($requests->isEmpty())
                            <div class="alert alert-warning text-center" role="alert">
                                <strong>ไม่พบรายการแจ้งซ่อม</strong>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">RequestID</th>
                                             <th scope="col">TicketID</th>
                                            <th scope="col">เวลา/วัน/เดือน/ปี</th>
                                            <th scope="col">สถานะ</th>
                                            <th scope="col">ชนิดอุปกรณ์</th>
                                            <th scope="col">เบอร์โทรศัพท์</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="requestTableBody">
                                        @foreach ($requests as $request)
                                            <tr data-status="{{ $request->status->Statusname ?? 'Unknown' }}" data-date="{{ \Carbon\Carbon::parse($request->Date)->format('Y-m-d') }}">
                                               <td>
                           <a href="#" class="ticket-number" data-ticket="{{ $request->TicketNumber }}" 
    data-tag="{{ $request->TagNumber }}" 
    data-date="{{ \Carbon\Carbon::parse($request->Date)->format('d/m/Y') }}" 
    data-repair="{{ $request->RepairDetail }}" 
    data-device="{{ $request->device->Devicename }}" 
    data-status="{{ $request->status->Statusname }}"
    data-name="{{ $request->user ? $request->user->name : 'Unknown User' }}"
    data-tel="{{ $request->Tel }}"
    data-building="{{ $request->registerTag->building->building ?? 'N/A' }}"  
    data-floor="{{ $request->registerTag->floor ?? 'N/A' }}"        
    data-room="{{ $request->registerTag->room ?? 'N/A' }}">
    {{ $request->TicketNumber }}
</a>
                        </td>
                         <td>
                                                    @if (!$request->TagNumber)
                                                        <a href="{{ route('repair_requests.showTagRegisterForm', $request->TicketID) }}"
                                                            class="btn btn-success btn-sm">ลงทะเบียน Ticket</a>
                                                    @else
                                                         <a href="{{ route('repair_requests.searchByTag', ['TagNumber' => $request->TagNumber]) }}">
        {{ $request->TagNumber }}
    </a>
                                                    @endif
                                                </td>
                                                  <td>{{ \Carbon\Carbon::parse($request->Date)->format('H:i/d/m/Y ') }}</td>
                                               
                                                <td>
                                                    @switch($request->status->Statusname ?? 'Unknown')
                                                        @case('Pending')
                                                            <span class="badge bg-warning text-dark">รอดำเนินการ</span>
                                                        @break

                                                        @case('รับเรื่อง')
                                                            <span class="badge bg-info text-white">รับเรื่องแล้ว</span>
                                                        @break

                                                        @case('กำลังดำเนินการ')
                                                            <span class="badge bg-primary text-white">กำลังดำเนินการ</span>
                                                        @break

                                                        @case('ดำเนินการแล้ว')
                                                            <span class="badge bg-success text-white">ดำเนินการแล้ว</span>
                                                        @break

                                                        @default
                                                            <span class="badge bg-secondary text-white">Unknown</span>
                                                    @endswitch
                                                </td>

                                                <td>{{ $request->device->Devicename ?? 'Unknown Device' }}</td>

                                                <td>{{ $request->Tel }}</td>
                                               <td>
   

    @if (in_array($request->StatusID, [0, 2])) <!-- Check if StatusID is 0 or 2 -->
        <a href="{{ route('repair_requests.showStatusChangeForm', $request->TicketID) }}"
            class="btn btn-info btn-sm">เปลี่ยนสถานะ</a>
    @endif
</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="repairDetailsModal" tabindex="-1" role="dialog" aria-labelledby="repairDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="repairDetailsModalLabel">รายละเอียดการแจ้งซ่อม</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
               <div class="modal-body">
    <p><strong>TicketID:</strong> <span id="modal-ticket"></span></p>
    <p><strong>วันเดือนปี:</strong> <span id="modal-date"></span></p>
    <p><strong>หมายเลข Tag:</strong> <span id="modal-tag"></span></p>
    <p><strong>อาการ:</strong> <span id="modal-repair"></span></p>
    <p><strong>อุปกรณ์:</strong> <span id="modal-device"></span></p>
    <p><strong>สถานะ:</strong> <span id="modal-status"></span></p>
    <p><strong>ชื่อผู้แจ้ง:</strong> <span id="modal-name"></span></p>
    <p><strong>เบอร์โทรศัพ:</strong> <span id="modal-tel"></span></p>


</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Filter based on status, month and year dynamically
            $('#status, #month, #year').on('change', function() {
                var selectedStatus = $('#status').val();
                var selectedMonth = $('#month').val();
                var selectedYear = $('#year').val();
                $('#requestTableBody tr').each(function() {
                    var status = $(this).data('status');
                    var date = $(this).data('date');
                    var showRow = true;

                    // Check status
                    if (selectedStatus && status !== selectedStatus) {
                        showRow = false;
                    }

                    // Check month and year
                    var requestDate = new Date(date);
                    var requestMonth = requestDate.getMonth() + 1; // Months are zero-based
                    var requestYear = requestDate.getFullYear();

                    if (selectedMonth && requestMonth !== parseInt(selectedMonth)) {
                        showRow = false;
                    }

                    if (selectedYear && requestYear !== parseInt(selectedYear)) {
                        showRow = false;
                    }

                    $(this).toggle(showRow);
                });
            });

            // Handle modal popup when clicking ticket number
          
      $('.ticket-number').on('click', function(event) {
    event.preventDefault();

    // ดึงข้อมูลจาก data-* attributes
    var ticket = $(this).data('ticket');
    var date = $(this).data('date');
    var tag = $(this).data('tag');
    var repair = $(this).data('repair');
    var device = $(this).data('device');
    var tel = $(this).data('tel');
    var name = $(this).data('name');
    var status = $(this).data('status');
    // เพิ่ม

    // นำข้อมูลไปใส่ใน modal
    $('#modal-ticket').text(ticket);
    $('#modal-date').text(date);
    $('#modal-tag').text(tag);
    $('#modal-repair').text(repair);
    $('#modal-device').text(device);
    $('#modal-tel').text(tel);
    $('#modal-name').text(name);
    $('#modal-status').text(status);


    // แสดง modal
    $('#repairDetailsModal').modal('show');
});
        });
    </script>
@endsection
