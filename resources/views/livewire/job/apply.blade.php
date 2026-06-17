<div class="container py-5">
    <div class="row">
        <!-- Sidebar: Employer & Job Summary -->
        <div class="col-lg-4 mb-4">
            <!-- Employer Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ $job->employer->logo ? Storage::url($job->employer->logo) : 'https://placehold.co/100x100?text=' . urlencode($job->employer->name) }}"
                            alt="{{ $job->employer->name }}" width="60" height="60" class="rounded border me-3">
                        <div>
                            <h5 class="mb-0">{{ $job->employer->name }}</h5>
                            <small class="text-muted">Employer</small>
                        </div>
                    </div>

                    <div class="small text-muted mb-3">
                        {!! $job->employer->description !!}
                    </div>

                    <hr class="my-3">

                    <div class="d-grid gap-2">
                        @if ($job->employer->email)
                            <div class="d-flex align-items-center small">
                                <i class="fi-rr-envelope me-2 text-primary"></i> {{ $job->employer->email }}
                            </div>
                        @endif
                        @if ($job->employer->phone)
                            <div class="d-flex align-items-center small">
                                <i class="fi-rr-phone-call me-2 text-primary"></i> {{ $job->employer->phone }}
                            </div>
                        @endif
                        @if ($job->employer->address)
                            <div class="d-flex align-items-start small">
                                <i class="fi-rr-marker me-2 text-primary mt-1"></i>
                                <span>{{ collect([$job->employer->address, $job->employer->city, $job->employer->country])->filter()->implode(', ') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Job Summary Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="text-uppercase text-muted fw-bold mb-3 small">Job Overview</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fi-rr-map-marker me-2"></i>Location</span>
                        <span class="fw-semibold">{{ $job->location }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fi-rr-briefcase me-2"></i>Type</span>
                        <span class="fw-semibold">{{ $job->job_type->getLabel() }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="fi-rr-money me-2"></i>Salary</span>
                        <span class="fw-semibold text-success">{{ $job->salary_range }} AED</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Form Area -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h3 class="mb-4 fw-bold">Apply for {{ $job->title }}</h3>
                    <p class="text-muted mb-4">Complete the form below to submit your application directly to the hiring
                        team.</p>

                    <form wire:submit="submit" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">First Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" wire:model.blur="first_name"
                                    class="form-control form-control-lg @error('first_name') is-invalid @enderror"
                                    required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Last Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model.blur="last_name"
                                    class="form-control form-control-lg @error('last_name') is-invalid @enderror"
                                    required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Email Address <span
                                        class="text-danger">*</span></label>
                                <input type="email" wire:model.blur="email"
                                    class="form-control form-control-lg @error('email') is-invalid @enderror" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium">Phone Number <span
                                        class="text-danger">*</span></label>
                                <input type="text" wire:model.blur="phone"
                                    class="form-control form-control-lg @error('phone') is-invalid @enderror" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-medium">Nationality <span
                                        class="text-danger">*</span></label>
                                <input type="text" wire:model.blur="nationality"
                                    class="form-control form-control-lg @error('nationality') is-invalid @enderror"
                                    required>
                            </div>

                            <!-- Optimized CV Upload Section -->
                            <div class="col-12 mb-4">
                                <label class="form-label fw-bold text-dark mb-2">Upload CV / Resume <span
                                        class="text-danger">*</span></label>

                                <label for="cv-upload"
                                    class="d-flex flex-column align-items-center justify-content-center w-100 p-4 border border-2 border-dashed rounded-3 bg-light cursor-pointer hover-border-primary transition-all">
                                    <!-- Default state -->
                                    <div wire:loading.remove wire:target="cv" class="text-center">
                                        <i class="fi-rr-cloud-upload fs-1 text-primary mb-2"></i>
                                        <span class="fw-medium text-muted d-block">Click to upload or drag and
                                            drop</span>
                                        <span class="text-xs text-secondary mt-1">PDF, DOC, DOCX (Max 5MB)</span>
                                    </div>

                                    <!-- Loading state for CV -->
                                    <div wire:loading wire:target="cv" class="text-center text-primary">
                                        <i class="fa fa-spinner fa-spin fs-1 mb-2"></i>
                                        <span class="fw-bold d-block">Uploading your file...</span>
                                    </div>

                                    <input type="file" id="cv-upload" wire:model="cv" class="d-none"
                                        accept=".pdf,.doc,.docx">
                                </label>

                                @if ($cv)
                                    <div class="mt-2 text-success small"><i class="fi-rr-check-circle me-1"></i> File
                                        selected: {{ $cv->getClientOriginalName() }}</div>
                                @endif
                                @error('cv')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-4">
                                <label class="form-label fw-medium">Cover Letter</label>
                                <textarea wire:model.blur="cover_letter" rows="5" class="form-control form-control-lg"
                                    placeholder="Tell us why you are a great fit..."></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 rounded-pill shadow-sm"
                            wire:loading.attr="disabled" wire:target="submit">
                            <span wire:loading.remove wire:target="submit">
                                <i class="fi-rr-paper-plane me-2"></i> Submit Application
                            </span>
                            <span wire:loading wire:target="submit">
                                <i class="fa fa-spinner fa-spin me-2"></i> Submitting...
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
