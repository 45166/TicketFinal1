@extends('layouts.app')  

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-primary fw-bold">รายการแจ้งซ่อม</h1>
        <a href="{{ route('repair_requests.create') }}" class="btn btn-primary btn-lg shadow-sm">+ แจ้งซ่อมใหม่</a>
    </div>

    @if($requests->isEmpty())
        <div class="alert alert-warning text-center" role="alert">
            <strong>ยังไม่ได้แจ้งซ่อม</strong>
        </div>
    @else
        <div class="d-flex justify-content-center">
            <div class="card shadow-sm border-primary w-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">รายการแจ้งซ่อม</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">RequestID</th>
                                     <th scope="col">TicketID</th>
                                    <th scope="col">เวลา/วัน/เดือน/ปี</th>
                                    <th scope="col">สถานะ</th>
                                    <th scope="col">ชนิดอุปกรณ์</th>
                                    <th scope="col" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $request)
                                    <tr class="clickable-row" data-toggle="collapse" data-target="#details-{{ $request->TicketID }}">
                                        <td>{{ $request->TicketNumber }}</td>
                                           <td>
                                            @if($request->TagNumber)
                                                <a href="{{ route('repair_requests.searchByTag', ['TagNumber' => $request->TagNumber]) }}">
                                                    {{ $request->TagNumber }}
                                                </a>
                                            @else
                                                <span class="text-muted">ยังไม่ลงทะเบียน</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($request->Date)->format('H:i/d/m/Y ') }}</td>

                                     
                                        <td>
                                            @switch($request->status->Statusname)
                                                @case('Pending')
                                                    <span class="badge bg-warning text-dark">รอดำเนินการ</span>
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
<td class="text-center">
    <div class="d-flex justify-content-center align-items-center">
        <!-- Block for the "ดูรายละเอียด" button -->
        <div class="me-2"> 
            @if($request->StatusID == 2 || $request->StatusID == 3) <!-- เงื่อนไขแสดงลิงก์ -->
                <a href="{{ route('repair_requests.showNote', $request->TicketID) }}">
                    <img src="{{ asset('images/search.png') }}" alt="Search" style="width: 30px; height: auto;">
                </a>
            @endif
        </div>
        
        <!-- Block for the evaluation icon -->
        @if($request->status->Statusname === 'ดำเนินการแล้ว' && !$request->is_evaluated)
            <div> <!-- ไอคอนประเมิน -->
                <a href="{{ route('repair_requests.evaluate', $request->TicketID) }}">
                    <img src="{{ asset('images/estimate.png') }}" alt="Evaluate" style="width: 30px; height: auto;">
                </a>
            </div>
        @else
            <!-- เพิ่มบล็อกว่างที่มีขนาดเท่ากับไอคอนเพื่อป้องกันการขยับ -->
            <div style="width: 30px;"></div>
        @endif
    </div>
</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($requests instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="d-flex justify-content-center">
                            {{ $requests->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@section('scripts')
<script>
    $(document).ready(function(){
        $('.clickable-row').click(function(){
            $(this).next('.collapse').collapse('toggle');
        });
    });
</script>
@endsection
@endsection
