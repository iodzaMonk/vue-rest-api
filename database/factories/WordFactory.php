<?php

namespace Database\Factories;

use App\Models\Word;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Word>
 */
class WordFactory extends Factory
{
    protected $model = Word::class;

    public function definition(): array
    {
        return [
            'word' => $this->faker->unique()->word(),
            'pronunciation' => $this->faker->word().' /'.$this->faker->word().'/',
            'part_of_speech' => $this->faker->randomElement(['noun', 'verb', 'adjective']),
            'meaning' => $this->faker->sentence(),
            'example_sentence' => $this->faker->sentence(10),
            'difficulty' => $this->faker->randomElement(['easy', 'medium', 'hard']),
            'image' => null,
        ];
    }
}
