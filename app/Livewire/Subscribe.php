<?php

namespace App\Livewire;

use App\Models\Subscriber;
use Livewire\Component;

class Subscribe extends Component
{
    public $email;

    protected $rules = [
        'email' => 'required|email|unique:subscribers,email',
    ];

    protected $messages = [
        'email.unique' => 'You are already subscribed to our newsletter.',
    ];

    public function subscribe()
    {
        $this->validate();

        Subscriber::create([
            'email' => $this->email,
        ]);

        $this->reset('email');

        session()->flash('message', 'Thank you for subscribing!');
    }

    public function render()
    {
        return view('livewire.subscribe');
    }
}
