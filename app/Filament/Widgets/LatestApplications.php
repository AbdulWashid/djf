<?php

namespace App\Filament\Widgets;

use App\Models\JobApplications;
use Filament\Widgets\TableWidget;
use Filament\Tables;
use Filament\Tables\Table;

class LatestApplications extends TableWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Latest Applications';

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                JobApplications::query()
                    ->with('opening')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Candidate')
                    ->formatStateUsing(fn ($record) => $record->first_name . ' ' . $record->last_name),

                Tables\Columns\TextColumn::make('opening.title')
                    ->label('Job')
                    ->limit(25),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->since(),
            ]);
    }
}