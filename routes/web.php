<?php

use App\Livewire\Employers;
use App\Livewire\Faqs;
use App\Livewire\JobDetails;
use App\Livewire\Job\Apply;
use App\Livewire\Job\ThankYou;
use App\Livewire\JobsComponent;
use App\Livewire\Remainder;
use App\Livewire\SuperDuper\BlogList;
use App\Livewire\SuperDuper\BlogDetails;
use App\Livewire\SuperDuper\Pages\ContactUs;
use App\Models\HomePageMeta;
use App\Models\StaticPage;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Lab404\Impersonate\Services\ImpersonateManager;

Route::middleware('auth:employer')
    ->prefix('employer')
    ->group(function () {
        Route::view('/dashboard', 'dashboard')
            ->name('employer.dashboard');
    });
Route::middleware('auth:candidate')
    ->prefix('candidate')
    ->group(function () {
        Route::view('/dashboard', 'dashboard')
            ->name('candidate.dashboard');
    });

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/', function () {
    $homeMeta = HomePageMeta::query()->latest()->first();

    return view('components.superduper.pages.newhome', [
        'homeMeta' => $homeMeta,
    ]);
})->name('home');

Route::get('/about-us', function () {
    $page = StaticPage::where('slug', 'about-us')->first();
    if ($page) {
        return view('page', ['page' => $page]);
    }
})->name('about-us');


Route::get('/job/{slug}/apply', Apply::class)->name('jobs.apply.form');

Route::get('/jobs/{slug}/thank-you', ThankYou::class)->name('jobs.apply.thankyou');

Route::get('/jobs', JobsComponent::class)->name('jobs');

Route::get('/jobs/{location}', JobsComponent::class)->name('jobs.location');

Route::get('/jobs/{category}', JobsComponent::class)->name('jobs.category');

Route::get('/jobs/{location}/{category_slug}', JobsComponent::class)->name('jobs.location.category');

Route::get('/job-categories', function () {
    return view('components.superduper.pages.job-categories');
})->name('job-categories');

Route::get('/employers/', Employers::class)->name('employers');

Route::get('/blogs', BlogList::class)->name('blog');

Route::get('/remainder', Remainder::class)->name('remainder');

Route::get('/faqs', Faqs::class)->name('faqs');

Route::get('/job/{slug}', JobDetails::class)->name('jobs.show');

Route::get('/blog/{slug}', BlogDetails::class)->name('blog.show');

Route::get('/contact-us', ContactUs::class)->name('contact-us');

Route::get('/coming-soon', function () {
    return view('components.superduper.pages.coming-soon');
})->name('coming-soon');

Route::post('/contact', [App\Http\Controllers\ContactController::class, 'submit'])->name('contact.submit');

Route::post('/admin/cache-clear', function () {
    Artisan::call('optimize:clear');

    Notification::make()->title('Application cache cleared successfully.')->success()->send();

    return back()->with('status', 'Application cache cleared successfully.');
})->middleware('auth')->name('admin.cache.clear');

Route::get('impersonate/leave', function () {
    if (!app(ImpersonateManager::class)->isImpersonating()) {
        return redirect('/');
    }
    app(ImpersonateManager::class)->leave();
    return redirect(session()->pull('impersonate.back_to'));
})->name('impersonate.leave')->middleware('web');

Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemap.html', [App\Http\Controllers\SitemapController::class, 'html'])->name('sitemap.html');
// Route::get('/robots.txt', [App\Http\Controllers\SitemapController::class, 'robots'])->name('robots');

require __DIR__.'/auth.php';

Route::get('/{slug}', function ($slug) {
    $page = StaticPage::where('slug', $slug)->where('status', 1)->first();
    $faqSchema = '';
    if ($page) {
        if ($page->faqs) {
            $mainEntity = [];
            foreach ($page->faqs as $faq) {
                $mainEntity[] = [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => strip_tags($faq['answer']),
                    ],
                ];
            }
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $mainEntity,
            ];
            $faqSchema = json_encode($schema);
        }

        return view('page', ['page' => $page, 'faqSchema' => $faqSchema]);
    } else {
        abort(404);
    }
})->where('slug', '^(?!about|contact).*$')->name('page');





//Route::get('/privacy-policy', function () {
//    return view('components.superduper.pages.coming-soon');.
//})->name('privacy-policy');
//
//Route::get('/terms-conditions', function () {
//    return view('components.superduper.pages.coming-soon');
//})->name('terms-conditions');