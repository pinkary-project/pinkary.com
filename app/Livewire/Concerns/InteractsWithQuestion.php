<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Question as QuestionModel;
use Livewire\Attributes\Computed;

trait InteractsWithQuestion
{
    use CanBeIgnorable;
    use CanBeLikeable;
    use CanBePinnable;
    use CanBeReportable;
    use ShouldHandleAuthorization;

    protected QuestionModel $questionModel;

    public function fromModel(QuestionModel $question): void
    {
        $this->questionModel = $question;
    }

    #[Computed]
    public function question(): string
    {
        return $this->questionModel->content;
    }

    #[Computed]
    public function answer(): string
    {
        return $this->questionModel->answer;
    }

    #[Computed]
    public function answeredAt(): string
    {
        return $this->questionModel->answered_at
            ->timezone(session()->get('timezone', 'UTC'))
            ->diffForHumans();
    }
}
