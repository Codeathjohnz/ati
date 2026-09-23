<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestQuestion extends Model
{
    use HasFactory;

    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice';

    public const TYPE_FILL_BLANK = 'fill_blank';

    protected $fillable = [
        'training_program_test_id',
        'type',
        'question_text',
        'options',
        'correct_answer',
        'points',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(TrainingProgramTest::class, 'training_program_test_id');
    }
}
