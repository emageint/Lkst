<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
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

        $maxDelegates = $booking->max_delegates ?? 1;
        $existingDelegates = [];


        $delegates = array_pad($existingDelegates, $maxDelegates, [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
        ]);

        $this->form->fill([
            'delegates' => array_slice($delegates, 0, $maxDelegates),
        ]);
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


        return $schema
            ->components([
                Form::make($delegateFields)
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

        $this->booking->update([
            'delegates_submitted' => true,
            'status' => \App\Enums\BookingStatus::Confirmed,
        ]);

        $this->redirect(route('thank-you'));
    }


    public function getLayout(): string
    {
        return 'components.layouts.filament-public';
    }
}

