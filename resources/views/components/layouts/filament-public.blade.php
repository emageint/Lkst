@php
    use Filament\Support\Enums\Width;

    $livewire ??= null;
    $renderHookScopes = $livewire?->getRenderHookScopes();
    $maxContentWidth ??= (filament()->getSimplePageMaxContentWidth() ?? Width::FourExtraLarge);

    if (is_string($maxContentWidth)) {
        $maxContentWidth = Width::tryFrom($maxContentWidth) ?? $maxContentWidth;
    }
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    @props([
        'after' => null,
        'heading' => null,
        'subheading' => null,
    ])

    <div class="fi-simple-layout">
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_START, scopes: $renderHookScopes) }}

   
        <div class="fi-simple-main-ctn">
            <main
                @class([
                    'fi-simple-main mx-auto px-4',
                    ($maxContentWidth instanceof Width) ? "fi-width-{$maxContentWidth->value}" : $maxContentWidth,
                ])
            >

                <header class="fi-simple-header">
                    @php
                        $isDarkMode = config('filament.dark_mode') && session('theme') === 'dark';
                    @endphp

                    <a href="{{ route('filament.admin.pages.dashboard') }}">
                        @if ($isDarkMode)
                            <img src="{{ asset('images/logo-light.png') }}" style="height: 3rem;" class="fi-logo"
                                 alt="Logo Dark">
                        @else
                            <img src="{{ asset('images/logo-dark.png') }}" style="height: 3rem;" class="fi-logo"
                                 alt="Logo Light">
                        @endif
                    </a>
                </header>

                {{ $slot }}
            </main>
        </div>


        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $renderHookScopes) }}
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_END, scopes: $renderHookScopes) }}
    </div>
</x-filament-panels::layout.base>
