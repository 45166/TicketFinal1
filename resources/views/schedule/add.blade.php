@extends('layouts.app')

@section('content')
    <div class="container">
        <form action="{{ URL('/create-schedule') }}" method="POST" class="needs-validation" novalidate>
            @csrf

            <!-- Hidden input to send the TicketID -->
            <input type="hidden" name="TicketID" value="{{ $ticket->TicketID }}">

            <div class="form-group">
                <label for="title">{{ __('Ticket Number') }}</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $ticket->TicketNumber }}" required readonly>
            </div>

            <div class="form-group">
                <label for="start">{{ __('เวลาเริ่ม') }}</label>
                <input type="datetime-local" class="form-control" id="start" name="start" required value="{{ now()->format('Y-m-d\TH:i') }}">
            </div>

      

            <div class="form-group">
                <label for="color">{{ __('สี') }}</label>
                <input type="color" id="color" name="color" class="form-control">
            </div>

            <button type="submit" class="btn btn-success">{{ __('บันทึก') }}</button>
        </form>
    </div>
@endsection