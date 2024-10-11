@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">บันทึก Ticket ID: {{ $request->TicketNumber }}</h5>
        </div>
         <div class="card-body">
            <div class="mb-3">
                <strong>ผู้รับผิดชอบ : </strong>
                {{ $assignment->user->name ?? 'ไม่มีผู้รับผิดชอบ' }}
            </div>
        <div class="card-body">
            <form id="noteForm" action="{{ route('repair_requests.storeNote', $request->TicketID) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="note" class="form-label">รายละเอียด</label>
                    <textarea name="note" id="note" class="form-control" rows="4"
                        @if(Auth::user()->role != 1 || $request->StatusID == 3) disabled @endif>{{ old('note', $request->note) }}</textarea>
                </div>

                @if(Auth::user()->role == 1 && $request->StatusID != 3)  {{-- IT --}}
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                @else
                    @if(Auth::user()->role == 0)
                        <a href="{{ route('admin') }}" class="btn btn-secondary">กลับ</a> {{-- ปุ่มกลับสำหรับ Admin --}}
                    @else
                        <a href="{{ route('repair_request.index') }}" class="btn btn-secondary">กลับ</a> {{-- ปุ่มกลับสำหรับ User --}}
                    @endif
                @endif
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('noteForm').onsubmit = function(event) {
        @if(Auth::user()->role == 1)
            var statusID = @json($request->StatusID);
            if (statusID != 3) {
                event.preventDefault(); // Prevent form submission
                Swal.fire({
                    title: 'ยืนยันการบันทึก?',
                    text: "คุณต้องการบันทึกหมายเหตุนี้หรือไม่?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'ใช่, บันทึก!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Submit the form
                        event.target.submit();
                    }
                });
            }
        @endif
    };
</script>
@endsection
@endsection
