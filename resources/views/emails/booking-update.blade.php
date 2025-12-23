@component('mail::message')

Hello,

Please click the button below to update your booking details:

@component('mail::button', ['url' => $url])
        Update Your Booking
@endcomponent


This link is secure and does not require logging in.

Thank you!
@endcomponent
