@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm border-primary">
        <div class="card-header bg-secondary text-white">
            <h5 class="card-title">ประวัติการแจ้งซ่อมของหมายเลข Ticket : {{ $tagNumber }}</h5>
        </div>
        <div class="card-body">
            @if($results->isEmpty())
                <p class="text-center">ไม่พบข้อมูลการแจ้งซ่อมสำหรับหมายเลขแท็กนี้</p>
            @else
                <!-- ใช้ table-responsive เพื่อทำให้ตารางเลื่อนได้ในแนวนอนเมื่อหน้าจอเล็ก -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">RequestID</th>
                                 <th scope="col">TicketID</th>
                                <th scope="col">เวลา/วัน/เดือน/ปี</th>
                                <th scope="col">สถานะ</th>
                                <th scope="col">อุปกรณ์</th>
                              <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $request)
                                <tr>
                                                            <td>
                          <a href="#" class="ticket-number" data-ticket="{{ $request->TicketNumber }}" 
    data-tag="{{ $request->TagNumber }}" 
    data-date="{{ \Carbon\Carbon::parse($request->Date)->format('d/m/Y') }}" 
    data-repair="{{ $request->RepairDetail }}" 
    data-device="{{ $request->device->Devicename }}" 
    data-status="{{ $request->status->Statusname }}"
    data-name="{{ $request->user ? $request->user->name : 'Unknown User' }}"
    data-tel="{{ $request->Tel }}">
    {{ $request->TicketNumber }}
</a>

                        </td>
                                    <td>{{ $request->TagNumber }}</td>
                                    <td>{{ \Carbon\Carbon::parse($request->Date)->format('H:i/d/m/Y ')}}</td>
                                    
                                    <td>
                                        @switch($request->status->Statusname)
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
                                                <span class="badge bg-secondary">Unknown</span>
                                        @endswitch
                                    </td>
                                   
                                    <td>{{ $request->device->Devicename ?? 'N/A' }}</td>
                                    <td>
                         <div class="me-2"> 
            @if($request->StatusID == 2 || $request->StatusID == 3) <!-- เงื่อนไขแสดงลิงก์ -->
                <a href="{{ route('repair_requests.showNote', $request->TicketID) }}">
                    <img src="{{ asset('images/search.png') }}" alt="Search" style="width: 30px; height: auto;">
                </a>
            @endif
            </td>
        </div>
                                 
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- ปุ่มกลับไปยังหน้า -->
            <div class="row mt-4">
                <div class="col text-center">
                    @if(Auth::user()->role == 2)
                        <a href="{{ route('repair_request.index') }}" class="btn btn-primary">กลับ</a>
                    @elseif(Auth::user()->role == 1)
                        <a href="{{ route('it') }}" class="btn btn-primary">กลับ</a>
                        
                    @elseif(Auth::user()->role == 0)
                        <a href="{{ route('admin') }}" class="btn btn-primary">กลับ</a>
                    @else
                        <a href="{{ route('home') }}" class="btn btn-primary">กลับไปยังหน้าแรก</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="repairDetailsModal" tabindex="-1" role="dialog" aria-labelledby="repairDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="repairDetailsModalLabel">รายละเอียดการแจ้งซ่อม</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  $(document).ready(function() {
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
