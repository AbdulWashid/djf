<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Lab404\Impersonate\Services\ImpersonateManager;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;
use App\Models\HomePageMeta;
use App\Models\StaticPage;
use App\Livewire\SuperDuper\Pages\ContactUs;


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

Volt::route('/job/{slug}/apply', 'pages.apply')->name('jobs.apply.form');

Volt::route('/jobs/{slug}/thank-you', 'pages.thank-you')->name('jobs.apply.thankyou');

Volt::route('/jobs', 'pages.jobs-component')->name('jobs');
Volt::route('/jobs/{location}', 'pages.jobs-component')->name('jobs.location');
Volt::route('/jobs/{category}', 'pages.jobs-component')->name('jobs.category');
Volt::route('/jobs/{category}/{category_slug}', 'pages.jobs-component')->name('jobs.location.category');

Route::get('/job-categories', function () {
    return view('components.superduper.pages.job-categories');
})->name('job-categories');

Volt::route('/employer', 'pages.employer')->name('employer');

Volt::route('/remainder', 'pages.remainder')->name('remainder');

Volt::route('/faqs', 'pages.faqs')->name('faqs');

Volt::route('/job/{slug}', 'pages.job-details')->name('jobs.show');

Volt::route('/blog/{slug}', 'pages.blog-detail')->name('blog.show');

Volt::route('/blogs', 'pages.blog-list')->name('blog');

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




// Route::get('/job/{slug}/apply', Apply::class)->name('jobs.apply.form');
// Route::get('/jobs/{slug}/thank-you', ThankYou::class)->name('jobs.apply.thankyou');
// Route::get('/jobs', JobsComponent::class)->name('jobs');
// Route::get('/jobs/{location}', JobsComponent::class)->name('jobs.location');
// Route::get('/jobs/{category}', JobsComponent::class)->name('jobs.category');
// Route::get('/jobs/{location}/{category_slug}', JobsComponent::class)->name('jobs.location.category');
// Route::get('/employers', Employers::class)->name('employers');
// Route::get('/faqs', Faqs::class)->name('faqs');
// Route::get('/remainder', Remainder::class)->name('remainder');
// Route::get('/job/{slug}', JobDetails::class)->name('jobs.show');
// Route::get('/blog/{slug}', BlogDetails::class)->name('blog.show');
// Route::get('/blogs', BlogList::class)->name('blog');
// Route::get('/robots.txt', [App\Http\Controllers\SitemapController::class, 'robots'])->name('robots');

//Route::get('/privacy-policy', function () {
//    return view('components.superduper.pages.coming-soon');
//})->name('privacy-policy');
//
//Route::get('/terms-conditions', function () {
//    return view('components.superduper.pages.coming-soon');
//})->name('terms-conditions');