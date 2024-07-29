<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Models\Question;
use App\Models\Tag;
use App\Models\User;
use Livewire\Component;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use App\Rules\NoBlankCharacters;
use Livewire\Attributes\Computed;

final class Create extends Component
{
    public $content = '';
    public $tags = [];

public function updatedContent($value)
{
    // Detect if '#' is pressed and show dropdown
    if (preg_match('/#(\w*)$/', $value)) {
    // if (preg_match('/\s#\w*$/', $value)) {
        dd(explode('#', $value)[1]);
        // $this->tags = Tag::where('name', 'like', '%' . explode('#', $value)[1] . '%')->limit(20)->get()->toArray();
    } else {
        $this->tags = [];
    }
}

public function selectTag($tagName)
{
    // Replace the current word with the selected tag
    $this->content = preg_replace('/#\w*$/', "#{$tagName} ", $this->content);
    $this->tags = [];
}

    public function submit()
    {
        $this->validate([
            'content' => 'required|string|max:255',
        ]);

        $update = Question::create(['content' => $this->content]);

        // Extract tags and attach to the update
        preg_match_all('/#(\w+)/', $this->content, $matches);
        $tags = $matches[1];

        foreach ($tags as $tagName) {
            $tag = Tag::firstOrCreate(['name' => $tagName]);
            $update->tags()->attach($tag);
        }

        // Clear the form
        $this->content = '';
    }

    public function render()
    {
        return view('livewire.questions.create', [
            'suggestedTags' => $this->tags,
        ]);
    }
}
