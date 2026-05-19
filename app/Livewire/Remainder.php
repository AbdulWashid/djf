<?php

namespace App\Livewire;

use Livewire\Component;

class Remainder extends Component
{
    public function render()
    {
        return view('livewire.remainder')->layout('components.frontend.main', [
            'pageType' => 'blog_post',
            'postTitle' => "Test",
            'postCategory' => $this->post->category->name ?? '',
            'authorName' => $this->post->author->name ?? '',
            'publishDate' => "",
            'pageDescription' => "",
            'metaKeywords' =>  '',
            'canonicalUrl' => '',
            'ogImage' => null,
        ]);;
    }
}
