<x-filament::page>
    @if($this->booking?->course || $this->booking?->start)
        <div style="margin-bottom: 1rem; font-size: 1rem;">
            @if($this->booking->course)
                <div><strong>Course:</strong> {{ $this->booking->course->name }}</div>
            @endif
            @if($this->booking->start)
                <div><strong>Date:</strong> {{ $this->booking->start->format('l, d F Y') }}</div>
                <div><strong>Start Time:</strong> {{ $this->booking->start->format('H:i') }}</div>
            @endif
            @if($this->booking->location_lkst_yard)
                <div><strong>Location:</strong>
                    London and Kent Safety Training At Ltd,
                    Knight's Place Equestrian,
                    Knight's Place Farm,
                    Cobham,
                    Kent,
                    ME2 3UB
                </div>
            @endif
        </div>
    @endif
    @if($this->booking?->price !== null)
        <div style="margin-bottom: 1.5rem; font-size: 1rem; font-weight: 600;">
            <strong>Price + VAT:</strong> {!! $this->booking->price !!}
        </div>
    @endif

    {{ $this->form }}
</x-filament::page>
