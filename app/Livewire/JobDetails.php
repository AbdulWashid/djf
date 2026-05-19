<?php

namespace App\Livewire;

use App\Models\JobApplications;
use App\Models\Opening;
use Livewire\Component;
use Livewire\WithFileUploads;

class JobDetails extends Component
{
    use WithFileUploads;
    public $slug;
    public $job;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $cv = null;

    public bool $showApplyModal = false;

    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone = '';
    public string $nationality = '';
    public ?string $cover_letter = null;

    public bool $applySuccess = false;
    public ?string $applySuccessMessage = null;


    public function mount($slug)
    {

        $this->job = Opening::where('slug', $slug)->where('status', 1)->with('employer')->first();
//dd($this->job->meta_keywords);
        if (!$this->job) {
            return redirect()->route('home')->with('error', 'Job not found. ');
        }


    }

    public function render()
    {

        return view('livewire.job-details')->layout('components.frontend.main', [
            'pageTitle' => $this->job->meta_title,
            'pageDescription' => $this->job->meta_description,
            'metaKeywords' => $this->job->meta_keywords,
            'ogTags' => $this->job->og_tags,
            'twitterTags' => $this->job->twitter_tags]);


    }

    public function openApplyModal(): void
    {
        $this->resetValidation();
        $this->applySuccess = false;
        $this->applySuccessMessage = null;
        $this->showApplyModal = true;
    }

    public function closeApplyModal(): void
    {
        $this->showApplyModal = false;
    }

    public function submitApplication(): void
    {
        $validated = $this->validate([
            'first_name'   => ['required', 'string', 'min:2', 'max:120'],
            'last_name'    => ['required', 'string', 'min:2', 'max:120'],
            'email'        => ['required', 'email:rfc,dns', 'max:190'],
            'phone'        => ['required', 'string', 'max:50'],
            'nationality'  => ['required', 'string', 'max:120'],
            'cover_letter' => ['nullable', 'string', 'max:2000'],
            'cv'           => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // 5MB
        ]);

        $resumePath = $this->cv->store('job-applications/resumes', 'public');

        JobApplications::create([
            'opening_id'   => $this->job->id,
            'first_name'   => $validated['first_name'],
            'last_name'    => $validated['last_name'],
            'email'        => $validated['email'],
            'phone'        => $validated['phone'],
            'cover_letter' => $validated['cover_letter'] ?? null,
            'resume_path'  => $resumePath,
            'nationality'  => $validated['nationality'],
            'status'       => 'pending',
        ]);

        $this->reset([
            'first_name',
            'last_name',
            'email',
            'phone',
            'nationality',
            'cover_letter',
            'cv',
        ]);

        $this->showApplyModal = false;

        $this->applySuccess = true;
        $this->applySuccessMessage = 'Your application has been submitted successfully.';
    }
}
