<?php

namespace App\Filament\Pages;

use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


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
                Form::make([
                    Wizard::make([
                        Step::make('Your Details')
                            ->schema(array_merge($locationFields, $delegateFields, [
                                Section::make('Purchase Order')
                                    ->columns(1)
                                    ->schema([
                                        TextInput::make('po_number')
                                            ->label('PO Number')
                                            ->helperText('Optional — enter your Purchase Order number if required for invoicing.')
                                            ->maxLength(255),
                                    ]),
                            ])),
                        Step::make('Terms & Conditions')
                            ->schema([
                                Placeholder::make('terms_content')
                                    ->label('')
                                    ->content(new HtmlString('
                                        <div style="font-size:0.775rem; line-height:1.6;">
                                            <h2 style="font-size:1rem; font-weight:700; margin-bottom:1rem;">Terms and Conditions for all Bookings</h2>
                                            <p style="font-weight:600; margin-top:1rem; margin-bottom:0.25rem;">Booking Terms:</p>
                                            <p style="margin-bottom:0.5rem;">No booking can be confirmed until a completed booking from is submitted. Submissions must be done within 48 hours of the initial request.</p>
                                            <ul style="list-style:disc; padding-left:1.5rem; margin-bottom:0.75rem;">
                                                <li style="margin-bottom:0.25rem;">New customers will be invoiced pro-forma</li>
                                                <li style="margin-bottom:0.25rem;">Invoice payment terms for NPORS accreditations are strictly 14 days from invoice date due to new NPORS requirements</li>
                                                <li style="margin-bottom:0.25rem;">London and Kent accreditation strictly 30 Days from invoice date.</li>
                                                <li style="margin-bottom:0.25rem;">Failure to meet the terms will lead to applications being cancelled due to restrictions with accrediting bodies and full cost of the invoice will be charged.</li>
                                            </ul>
                                            <p style="font-weight:600; margin-top:1rem; margin-bottom:0.25rem;">Cancellation Terms:</p>
                                            <p style="margin-bottom:0.5rem;">Our cancellation terms are not including start date or day of cancellation</p>
                                            <ul style="list-style:disc; padding-left:1.5rem; margin-bottom:0.75rem;">
                                                <li style="margin-bottom:0.25rem;">2 Weeks plus notice – No charge</li>
                                                <li style="margin-bottom:0.25rem;">1-2 weeks\' notice – 25% of course fee</li>
                                                <li style="margin-bottom:0.25rem;">24 hours – 1 Week notice – 50% of course fee</li>
                                                <li style="margin-bottom:0.25rem;">Less than 24 hours – 75% of course fee</li>
                                                <li style="margin-bottom:0.25rem;">No shows – 100% of course fee</li>
                                            </ul>
                                            <p style="margin-bottom:0.5rem;">Bookings may be cancelled with no charge within one hour of the booking being made. Bookings can be moved to an alternative date but a charge may be made, if then the course is cancelled again then full original course fee will be made as per above cancelation terms, notice is given however any fees occurred by London and Kent Safety Training at Ltd resulting from the date change e.g. hotel fees or booked instructor fees will be chargeable</p>
                                            <p style="font-weight:600; margin-top:1rem; margin-bottom:0.25rem;">General Terms &amp; Conditions</p>
                                            <p style="margin-bottom:0.5rem;">Any fees occurred by London and Kent Safety Training at Ltd resulting from cancellations e.g. hotel fees or instructor fees will be chargeable</p>
                                            <p style="margin-bottom:0.5rem;">London and Kent Safety training at Ltd reserves the right to cancel or move the course to an alternative date</p>
                                            <p style="margin-bottom:0.5rem;">Upon signing the personal details form the delegate is giving their permission for their photograph to be taken on the day of the course and stored to enable us to issue their photo ID card.</p>
                                            <p style="margin-bottom:0.5rem;">Upon signing the personal details for the delegate is confirming that all information provided within the form is true and correct at the time of completion to the best of their knowledge.</p>
                                            <p style="margin-bottom:0.5rem;">Any information given prior to the course must be adhered to e.g. information of PPE to be worn we do not supply any PPE to the candidates and would expect them to supply their own. All information and instruction given to the delegates during the course must be adhered to ensure their safety.</p>
                                        </div>
                                    ')),
                                Checkbox::make('terms_accepted')
                                    ->label('I have read and agree to the Terms and Conditions')
                                    ->accepted()
                                    ->required()
                                    ->validationMessages([
                                        'accepted' => 'You must accept the Terms and Conditions to proceed.',
                                    ]),
                            ]),
                    ])
                        ->submitAction(
                            Action::make('save')
                                ->label('Submit')
                                ->submit('save')
                                ->keyBindings(['mod+s'])
                        ),
                ])
                    ->livewireSubmitHandler('save'),
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
            'po_number' => $data['po_number'] ?? null,
        ];

        if (!$this->booking->location_lkst_yard) {
            $bookingUpdate['training_location_line1'] = $data['training_location_line1'] ?? null;
            $bookingUpdate['training_location_line2'] = $data['training_location_line2'] ?? null;
            $bookingUpdate['training_location_line3'] = $data['training_location_line3'] ?? null;
            $bookingUpdate['training_location_city'] = $data['training_location_city'] ?? null;
            $bookingUpdate['training_location_postcode'] = $data['training_location_postcode'] ?? null;
        }

        $this->booking->update($bookingUpdate);

        $this->booking->load(['course', 'delegates']);
        $additionalEmails = $this->booking->customer->emailRecipients->pluck('email')->all();
        Mail::to($this->booking->customer->email)
            ->cc($additionalEmails)
            ->send(new BookingConfirmationMail($this->booking));

        $this->redirect(route('thank-you'));
    }


    public function getLayout(): string
    {
        return 'components.layouts.filament-public';
    }
}

