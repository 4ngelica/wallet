<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UserFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['individual', 'company']);
        $name = ($type == 'individual') ? fake()->unique()->name() : fake()->unique()->company();
        $document = ($type == 'individual') ? fake()->unique()->cpf(false) : fake()->unique()->cnpj(false);

        return [
            'name' => $name,
            'email' => strtolower(str_replace(' ', '', $name)) . '@example.com',
            'document'=> $document,
            'type' => $type,
            'password' => '1234'
        ];  

    }

}
