<?php

namespace Database\Factories;

use App\Models\VirtualHost;
use Illuminate\Database\Eloquent\Factories\Factory;

class VirtualHostFactory extends Factory
{
    protected $model = VirtualHost::class;

    public function definition(): array
    {
        return [
            'server_name' => fake()->unique()->domainName(),
            'document_root' => 'D:/www/' . fake()->slug(),
            'ssl_enabled' => fake()->boolean(),
            'port' => fake()->randomElement([80, 8080]),
            'notes' => fake()->optional()->sentence(),
            'github_url' => fake()->optional()->url(),
        ];
    }
}
