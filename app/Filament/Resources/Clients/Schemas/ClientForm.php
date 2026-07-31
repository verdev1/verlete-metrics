<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                    ->selectablePlaceholder(false),

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