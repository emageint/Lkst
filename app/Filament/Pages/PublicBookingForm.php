<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;

use Filament\Forms\Form as FilamentForm;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use App\Models\Booking;
use Illuminate\Http\Request;
use Filament\Actions\Action;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;

class PublicBookingForm extends Page
{
    use InteractsWithForms;

    public ?array $data = [];
    public ?Booking $booking = null;
    protected string $view = 'filament.pages.public-booking-form';
    protected static ?string $title = '';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(Request $request, Booking $booking): void
    {
        $this->booking = $booking;
        if ($booking->training_location_line1) {
            $this->redirect(route('thank-you'));
            return;
        }
        $this->form->fill($booking->toArray());

    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    TextInput::make('training_location_line1')
                        ->required()
                        ->maxLength(255),

                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->submit('save')
                                ->keyBindings([ 'mod+s' ]),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }


    public function save(): void
    {
        $data = $this->form->getState();

        $this->booking->update($data);

        Notification::make()
            ->success()
            ->title('Saved')
            ->send();
    }

    public function getLayout(): string
    {
        return 'components.layouts.filament-public';
//        $this->maxContentWidth = \Filament\Support\Enums\Width::FourExtraLarge;
//        return 'filament-panels::components.layout.simple';
    }
}
