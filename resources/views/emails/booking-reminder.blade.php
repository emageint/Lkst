@component('mail::message')

Hello,

This is a friendly reminder that your booking details are still pending. Please click the button below to update them before the link expires:

@component('mail::button', ['url' => $url])
        Update Your Booking
@endcomponent


This link is secure and does not require logging in.

Thank you!
@endcomponent
