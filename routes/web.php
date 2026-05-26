<?php

use App\Livewire\Employers;
use App\Livewire\Faqs;
use App\Livewire\JobDetails;
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

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
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
    //  return view('components.superduper.pages.about');
})->name('about-us');

Route::get('/jobs', JobsComponent::class)->name('jobs');
Route::get('/jobs/{location}', JobsComponent::class)->name('jobs.location');
Route::get('/jobs/{category}', JobsComponent::class)->name('jobs.category');
Route::get('/jobs/{location}/{category_slug}', JobsComponent::class)->name('jobs.location.category');
// Route::get('/jobs/{location}/category/{category}', JobsComponent::class)->name('jobs.location.category');

Route::get('/job-categories', function () {
    return view('components.superduper.pages.job-categories');
})->name('job-categories');

Route::get('/employers/', Employers::class)->name('employers');
Route::get('/blogs', BlogList::class)->name('blog');
Route::get('/remainder', Remainder::class)->name('remainder');
Route::get('/faqs', Faqs::class)->name('faqs');
Route::get('/job/{slug}', Jobdetails::class)->name('jobs.show');
Route::get('/blog/{slug}', BlogDetails::class)->name('blog.show');
Route::get('/contact-us', ContactUs::class)->name('contact-us');
//Route::get('/privacy-policy', function () {
//    return view('components.superduper.pages.coming-soon');.
//})->name('privacy-policy');
//
//Route::get('/terms-conditions', function () {
//    return view('components.superduper.pages.coming-soon');
//})->name('terms-conditions');
Route::get('/coming-soon', function () {
    return view('components.superduper.pages.coming-soon');
})->name('coming-soon');
Route::post('/contact', [App\Http\Controllers\ContactController::class, 'submit'])->name('contact.submit');

Route::post('/admin/cache-clear', function () {
    Artisan::call('optimize:clear');
    // Artisan::call('filament:optimize-clear');
    // Artisan::call('filament-icons:clear-cache');

    Notification::make()->title('Application cache cleared successfully.')->success()->send();

    return back()->with('status', 'Application cache cleared successfully.');
})
    ->middleware('auth')
    ->name('admin.cache.clear');

Route::get('impersonate/leave', function () {
    if (!app(ImpersonateManager::class)->isImpersonating()) {
        return redirect('/');
    }
    app(ImpersonateManager::class)->leave();
    return redirect(session()->pull('impersonate.back_to'));
})
    ->name('impersonate.leave')
    ->middleware('web');
// SEO Routes
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemap.html', [App\Http\Controllers\SitemapController::class, 'html'])->name('sitemap.html');
// Route::get('/robots.txt', [App\Http\Controllers\SitemapController::class, 'robots'])->name('robots');

Route::get('/{slug}', function ($slug) {
    // Check for a page with the matching slug
    $page = StaticPage::where('slug', $slug)->first();
    $faqSchema = '';
    // If a page is found, return its view
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
    }
    // If no page with that slug exists, let the route fall through
    // to the next, which is the fallback route.
})
    ->where('slug', '^(?!about|contact).*$')
    ->name('page');
