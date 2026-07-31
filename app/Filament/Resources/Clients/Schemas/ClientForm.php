<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Client name')
                    ->required()
                    ->maxLength(100),

                TextInput::make('website')
                    ->url()
                    ->maxLength(100)
                    ->placeholder('https://example.com'),

                TextInput::make('analytics_property')
                    ->label('Analytics property')
                    ->maxLength(100)
                    ->placeholder('GA4 property ID'),

                Select::make('store')
                    ->options([
                        'none' => 'None',
                        'woocommerce' => 'WooCommerce',
                        'surecart' => 'SureCart',
                    ])
                    ->default('none')
                    ->required()
                    ->selectablePlaceholder(false)
                    ->live(),

                TextInput::make('surecart_api_key')
                    ->label('SureCart API key')
                    ->password()
                    ->revealable()
                    ->autocomplete('new-password')
                    ->helperText('API key used to connect to SureCart.')
                    ->visible(fn (Get $get): bool => $get('store') === 'surecart'),

                TextInput::make('application_username')
                    ->label('Application username')
                    ->maxLength(100)
                    ->autocomplete(false)
                    ->helperText('Username used to connect to the website API.'),

                TextInput::make('application_password')
                    ->label('Application password')
                    ->password()
                    ->revealable()
                    ->autocomplete('new-password')
                    ->helperText('Use a dedicated WordPress Application Password.'),

                TextInput::make('recipient_names')
                    ->label('Recipient names')
                    ->maxLength(255)
                    ->helperText('Enter recipient names. Eg: “Hi Sam and Alex”.'),

                TextInput::make('emails')
                    ->label('Email addresses')
                    ->maxLength(255)
                    ->helperText('Separate multiple email addresses with commas.'),

                Textarea::make('message')
                    ->rows(5)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}