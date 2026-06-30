<?php

namespace App\Filament\Widgets;

use App\Models\ContactUs;
use Filament\Widgets\TableWidget;
use Filament\Tables;
use Filament\Tables\Table;

class LatestMessages extends TableWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Latest Contact Messages';

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ContactUs::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject')
                    ->limit(30),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->since(),
            ]);
    }
}