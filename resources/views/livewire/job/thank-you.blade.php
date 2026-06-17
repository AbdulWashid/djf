<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4 p-md-5">

                <!-- Success Icon -->
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle"
                        style="width: 80px; height: 80px;">
                        <svg width="80px" height="80px" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#00f925"
                                d="M512 64a448 448 0 1 1 0 896 448 448 0 0 1 0-896zm-55.808 536.384-99.52-99.584a38.4 38.4 0 1 0-54.336 54.336l126.72 126.72a38.272 38.272 0 0 0 54.336 0l262.4-262.464a38.4 38.4 0 1 0-54.272-54.336L456.192 600.384z" />
                        </svg>
                    </div>
                </div>

                <!-- Content -->
                <h2 class="fw-bold mb-3">Application Received!</h2>
                <p class="text-muted fs-5">
                    Hello <strong>{{ $application->first_name }}</strong>, your application for
                    <span class="text-dark fw-semibold">{{ $job->title }}</span> has been successfully submitted.
                </p>

                <div class="bg-light p-3 rounded-3 my-4 text-start">
                    <p class="mb-1 text-muted small text-uppercase fw-bold">Next Steps</p>
                    <p class="mb-0 small text-secondary">
                        Our recruitment team is currently reviewing your profile. If your skills and experience match
                        the requirements, we will reach out to you directly at
                        <strong>{{ $application->email }}</strong>.
                    </p>
                </div>

                <!-- Actions -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-3">
                    <a href="{{ route('jobs') }}" class="btn btn-outline-primary btn-lg px-4 rounded-pill">
                        Browse More Jobs
                    </a>
                    <a href="{{ route('jobs.show', $job->slug) }}" class="btn btn-primary btn-lg px-4 rounded-pill">
                        View Job Details
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
