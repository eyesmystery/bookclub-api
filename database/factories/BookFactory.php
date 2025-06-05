<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name(),
            'description' => $this->faker->paragraph(3),
            'cover_image' => $this->faker->imageUrl(400, 600, 'books'),
            'pdf_file' => $this->faker->url() . '/book.pdf',
            'recommended_by_user_id' => User::factory(),
        ];
    }
}
