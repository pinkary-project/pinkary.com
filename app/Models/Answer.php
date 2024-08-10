<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\ParsableContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $content
 * @property int $question_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Question $question
 */
final class Answer extends Model
{
    use HasFactory;

    protected $casts = [
        'content' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // fix all refernces to answer
    //  * @property Carbon|null $answer_created_at
    // * @property Carbon|null $answer_updated_at

    // NOTE: first up handle the migration of data & the database schema
    //  we need to add the columns needed to handle the new entity
    //  then transfer the data from the old answer entity to the new entity
    //  then remove the old deprecated columns for the remaining seperate entity
    //  then update the code to use the new entity
    //  WE need to be mindful that where there is an __UPDATE__ in the content field we need to handle this
    //  we will set the is_update column to true for these records & move the data from the answer field, into the content field
    //  only when there is an actual question being asked, will we create an answer entity

    // Will need to fix the way mentions are done, they will need to be polymorphic

    // We will need to fix the following:
    // 1) Question observer
    // 2) Question Update livewire component
    // 3) Question factory
    // We need to deal with the fact we were using the question model content to store __UPDATE__ content
    // If the __UPDATE__ content is then it should be stored elsewhere.
    // Because we are changing the content field to be nullable we need to update the QuestionObserver to handle this

    // Should we just extract the __UPDATE__ content to a new entity?

    // answer_created_at -> answer->created_at
    // answer_updated_at -> answer->updated_at
    // answer (string) -> answer->content
    // if the content is __UPDATE__, the answer should be moved to the content
    // we need a flag column for update. is_update.

    // I'll spend a few hours writing out a structure for how we can migrate answers to a seperate entity.
    // not need to use the __UPDATE__ in the content field. Might take a few days but i'll put together a POC.

    /**
     * The attributes that should be cast.
     */
    public function getContentAttribute(?string $value): ?string
    {
        $content = new ParsableContent();

        return $value !== null && $value !== '' && $value !== '0' ? $content->parse($value) : null;
    }

    /**
     * The answer's question.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * The user that owns the answer. to_id on the question.
     */
    public function owner(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, Question::class, 'id', 'id', 'question_id', 'to_id');
    }
}
