<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


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
        if ($booking->delegates_submitted) {
            $this->redirect(route('thank-you'));
            return;
        }

        if (
            $booking->status === \App\Enums\BookingStatus::Expired
            || ($booking->form_expires_at !== null && $booking->form_expires_at->isPast())
        ) {
            $booking->updateQuietly([ 'status' => \App\Enums\BookingStatus::Expired ]);
            $this->redirect(route('booking.expired'));
            return;
        }


        $maxDelegates = $booking->max_delegates ?? 1;
        $existingDelegates = [];


        $delegates = array_pad($existingDelegates, $maxDelegates, [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
        ]);

        $formData = [
            'delegates' => array_slice($delegates, 0, $maxDelegates),
        ];

        if (!$booking->location_lkst_yard) {
            $formData['training_location_line1'] = $booking->training_location_line1 ?? '';
            $formData['training_location_line2'] = $booking->training_location_line2 ?? '';
            $formData['training_location_line3'] = $booking->training_location_line3 ?? '';
            $formData['training_location_city'] = $booking->training_location_city ?? '';
            $formData['training_location_postcode'] = $booking->training_location_postcode ?? '';
        }

        $this->form->fill($formData);
    }

    public function form(Schema $schema): Schema
    {
        $maxDelegates = $this->booking?->max_delegates ?? 1;


        $delegateFields = [];
        for ($i = 0; $i < $maxDelegates; $i++) {
            $num = $i + 1;
            $required = $i === 0;
            $delegateFields[] = \Filament\Schemas\Components\Section::make("Delegate {$num}")
                ->columns(3)
                ->schema([
                    TextInput::make("delegates.{$i}.first_name")
                        ->label('First Name')
                        ->required($required)
                        ->rules($required ? [] : [ "required_with:data.delegates.{$i}.last_name,data.delegates.{$i}.email" ]),
                    TextInput::make("delegates.{$i}.last_name")
                        ->label('Last Name')
                        ->required($required)
                        ->rules($required ? [] : [ "required_with:data.delegates.{$i}.first_name,data.delegates.{$i}.email" ]),
                    TextInput::make("delegates.{$i}.email")
                        ->label('Email')
                        ->email()
                        ->required($required)
                        ->rules($required ? [] : [ "required_with:data.delegates.{$i}.first_name,data.delegates.{$i}.last_name" ]),
                ]);
        }


        $locationFields = [];
        if (!$this->booking?->location_lkst_yard) {
            $locationFields[] = Section::make('Training Location')
                ->columns(2)
                ->schema([
                    TextInput::make('training_location_line1')
                        ->label('Address Line 1')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('training_location_line2')
                        ->label('Address Line 2')
                        ->maxLength(255),
                    TextInput::make('training_location_line3')
                        ->label('Address Line 3')
                        ->maxLength(255),
                    TextInput::make('training_location_city')
                        ->label('City')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('training_location_postcode')
                        ->label('Postcode')
                        ->required()
                        ->maxLength(255),
                ]);
        }

        return $schema
            ->components([
                Form::make(array_merge($locationFields, $delegateFields))
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Submit')
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
        $delegates = $data['delegates'] ?? [];

        foreach ($delegates as $delegateData) {
            if (empty($delegateData['email'])) {
                continue;
            }

            $user = User::withTrashed()->where('email', $delegateData['email'])->first();

            if ($user && $user->trashed()) {
                $user->restore();
            }

            if (!$user) {
                $user = new User();
                $user->first_name = $delegateData['first_name'];
                $user->last_name = $delegateData['last_name'];
                $user->email = $delegateData['email'];
                $user->password = Hash::make(Str::random(16));
                $user->save();
            }

            if (!$user->hasRole('Learner')) {
                $user->assignRole('Learner');
            }

            $this->booking->delegates()->syncWithoutDetaching([ $user->id ]);
        }

        $bookingUpdate = [
            'delegates_submitted' => true,
            'status' => \App\Enums\BookingStatus::Confirmed,
        ];

        if (!$this->booking->location_lkst_yard) {
            $bookingUpdate['training_location_line1'] = $data['training_location_line1'] ?? null;
            $bookingUpdate['training_location_line2'] = $data['training_location_line2'] ?? null;
            $bookingUpdate['training_location_line3'] = $data['training_location_line3'] ?? null;
            $bookingUpdate['training_location_city'] = $data['training_location_city'] ?? null;
            $bookingUpdate['training_location_postcode'] = $data['training_location_postcode'] ?? null;
        }

        $this->booking->update($bookingUpdate);

        $this->redirect(route('thank-you'));
    }


    public function getLayout(): string
    {
        return 'components.layouts.filament-public';
    }
}

