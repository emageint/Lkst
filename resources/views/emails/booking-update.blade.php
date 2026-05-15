@component('mail::message')

Hello,

Please find the details of your upcoming booking below:

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

@endif
Please click the button below to update your booking details:

@component('mail::button', ['url' => $url])
Update Your Booking
@endcomponent


This link is secure and does not require logging in.

---

<small>

**Terms and Conditions for all Bookings**

**Booking Terms:**

No booking can be confirmed until a completed booking from is submitted. Submissions must be done within 48 hours of the initial request.

- New customers will be invoiced pro-forma
- Invoice payment terms for NPORS accreditations are strictly 14 days from invoice date due to new NPORS requirements
- London and Kent accreditation strictly 30 Days from invoice date.
- Failure to meet the terms will lead to applications being cancelled due to restrictions with accrediting bodies and full cost of the invoice will be charged.

**Cancellation Terms:**

Our cancellation terms are not including start date or day of cancellation

- 2 Weeks plus notice – No charge
- 1-2 weeks' notice – 25% of course fee
- 24 hours – 1 Week notice – 50% of course fee
- Less than 24 hours – 75% of course fee
- No shows – 100% of course fee

Bookings may be cancelled with no charge within one hour of the booking being made. Bookings can be moved to an alternative date but a charge may be made, if then the course is cancelled again then full original course fee will be made as per above cancelation terms, notice is given however any fees occurred by London and Kent Safety Training at Ltd resulting from the date change e.g. hotel fees or booked instructor fees will be chargeable

**General Terms & Conditions**

Any fees occurred by London and Kent Safety Training at Ltd resulting from cancellations e.g. hotel fees or instructor fees will be chargeable

London and Kent Safety training at Ltd reserves the right to cancel or move the course to an alternative date

Upon signing the personal details form the delegate is giving their permission for their photograph to be taken on the day of the course and stored to enable us to issue their photo ID card.

Upon signing the personal details for the delegate is confirming that all information provided within the form is true and correct at the time of completion to the best of their knowledge.

Any information given prior to the course must be adhered to e.g. information of PPE to be worn we do not supply any PPE to the candidates and would expect them to supply their own. All information and instruction given to the delegates during the course must be adhered to ensure their safety.

</small>

Thank you!
@endcomponent
