<section class="section-box mt-50 mb-60">
    <div class="container">
        <div class="box-newsletter">
            <h5 class="text-md-newsletter">Sign up to get</h5>
            <h6 class="text-lg-newsletter">the latest jobs</h6>
            <div class="box-form-newsletter mt-30">
                <form class="form-newsletter" wire:submit.prevent="subscribe">
                    <input type="email" class="input-newsletter" wire:model="email"
                           placeholder="Enter your email...."/>
                    <button type="submit" class="btn btn-default font-heading icon-send-letter">Subscribe</button>
                </form>
                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                @if (session()->has('message'))
                    <div class="alert alert-success mt-2">
                        {{ session('message') }}
                    </div>
                @endif
            </div>
        </div>
        <div class="box-newsletter-bottom">
            <div class="newsletter-bottom"></div>
        </div>
    </div>
</section>
