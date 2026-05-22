@component('mail::message')

Hello,

Your booking has been updated. Please find the latest details below:

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

---

**What changed:**

@foreach($changes as $change)
- {{ $change }}
@endforeach

If you have any questions, please don't hesitate to get in touch.

Thank you!
@endcomponent
