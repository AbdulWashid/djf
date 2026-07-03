<?php

namespace App\Filament\Widgets;

use App\Models\Candidate;
use App\Models\ContactUs;
use App\Models\Employer;
use App\Models\JobApplications;
use App\Models\Opening;
use App\Models\Subscriber;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [

            Stat::make('Total Jobs', Opening::count())
                ->description(
                    Opening::whereDate('created_at', today())->count() . ' added today'
                )
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary')
                ->url(route('filament.admin.resources.jobs.index')),

            Stat::make('Employers', Employer::count())
                ->description(
                    Employer::where('is_active', true)->count() . ' Active'
                )
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success')
                ->url(route('filament.admin.resources.employers.index')),

            Stat::make('Candidates', Candidate::count())
                ->description(
                    Candidate::whereNotNull('email_verified_at')->count() . ' Verified'
                )
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->url(route('filament.admin.resources.candidates.index')),

            Stat::make('Applications', JobApplications::count())
                ->description(
                    JobApplications::where('status', 'pending')->count() . ' Pending'
                )
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning')
                ->url(route('filament.admin.resources.job-applications.index')),

            Stat::make('Subscribers', Subscriber::count())
                ->description(
                    Subscriber::whereDate('created_at', today())->count() . ' Today'
                )
                ->descriptionIcon('heroicon-m-envelope')
                ->color('purple')
                ->url(route('filament.admin.resources.subscribers.index')),

            Stat::make('Inbox', ContactUs::count())
                ->description(
                    ContactUs::where('status', 'new')->count() . ' Unread'
                )
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('danger')
                ->url(route('filament.admin.resources.inbox.index')),
        ];
    }
}