@component('mail::message')

Hello,

Thank you for submitting your booking details. Here is a summary of your booking:

---

@if($booking->course)
**Course:** {{ $booking->course->name }}

@endif
@if($booking->start)
**Date:** {{ $booking->start->format('l, d F Y') }}

**Start Time:** {{ $booking->start->format('H:i') }}

@endif
@if($booking->location_lkst_yard)
**Location:**
London and Kent Safety Training At Ltd,
Knight's Place Equestrian,
Knight's Place Farm,
Cobham,
Kent,
ME2 3UB

@elseif($booking->training_location)
**Location:** {{ $booking->training_location }}

@endif
@if($booking->price)
**Price + VAT:** {!! strip_tags($booking->price) !!}

@endif
@if($booking->po_number)
**PO Number:** {{ $booking->po_number }}

@endif

---

@if($booking->delegates->isNotEmpty())
**Delegates:**

@foreach($booking->delegates as $delegate)
- {{ $delegate->first_name }} {{ $delegate->last_name }} ({{ $delegate->email }}@if($delegate->phone), Tel: {{ $delegate->phone }}@endif)
@endforeach

@endif

If you have any questions, please don't hesitate to get in touch.

Thank you!
@endcomponent
