<?php

namespace App\Filament\Resources\JobApplicationsResource\Pages;

use App\Filament\Resources\JobApplicationsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Facades\Storage;

class ViewJobApplications extends ViewRecord
{
    protected static string $resource = JobApplicationsResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Grid::make(12)->schema([
                Group::make()
                    ->columnSpan(8)
                    ->schema([
                        Section::make('Applicant')
                            ->icon('heroicon-o-user')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('opening.title')->label('Job'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'reviewed' => 'info',
                                        'shortlisted' => 'success',
                                        'rejected' => 'danger',
                                        'hired' => 'success',
                                        default => 'gray',
                                    }),

                                TextEntry::make('first_name')->label('First name'),
                                TextEntry::make('last_name')->label('Last name'),

                                TextEntry::make('email')->copyable(),
                                TextEntry::make('phone')->copyable(),

                                TextEntry::make('nationality'),
                            ]),

                        Section::make('Cover Letter')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                TextEntry::make('cover_letter')
                                    ->placeholder('-')
                                    ->prose(),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(4)
                    ->schema([
//                        Section::make('Resume')
//                            ->icon('heroicon-o-paper-clip')
//                            ->schema([
//                                TextEntry::make('resume_path')
//                                    ->label('File')
//                                    ->formatStateUsing(fn (?string $state) => $state ? basename($state) : '-'),
//
//                                TextEntry::make('resume_url')
//                                    ->label('Download')
//                                    ->state(function ($record) {
//                                        if (!$record?->resume_path) {
//                                            return null;
//                                        }
//
//                                        return Storage::disk('public')->url($record->resume_path);
//                                    })
//                                    ->url(fn (?string $state) => $state ?: null, true)
//                                    ->openUrlInNewTab()
//                                    ->placeholder('-'),
//                            ]),

                        Section::make('Meta')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                TextEntry::make('created_at')->dateTime('M d, Y H:i'),
                                TextEntry::make('updated_at')->dateTime('M d, Y H:i'),
                            ]),
                    ]),
            ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadResume')
                ->label('Download CV')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn () => $this->record->resume_path ? Storage::disk('public')->url($this->record->resume_path) : null, true)
                ->openUrlInNewTab()
                ->visible(fn () => (bool) $this->record->resume_path),

            Actions\EditAction::make(),
        ];
    }
}
