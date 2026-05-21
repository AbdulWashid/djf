<?php

namespace App\Livewire;

use App\Models\Blog\Post;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class RecentPostHome extends Component
{
    public $posts;
    public function render()
    {
        $this->posts = Cache::remember('recent_posts_home', now()->addMinutes(30), function () {
            return Post::published()
                //                ->where('id', '!=', $this->post->id)
                ->select(['id', 'title', 'slug', 'blog_category_id', 'published_at', 'content_overview','blog_author_id'])
                ->with([
                    'category:id,name,slug',
                    'media' => function ($query) {
                        $query->where('collection_name', 'featured');
                    },
                    'author:id,username,firstname',
                ])
                ->orderBy('published_at', 'desc')
                ->limit(5)
                ->get();
        });

        return view('livewire.recent-post-home');
    }
}
