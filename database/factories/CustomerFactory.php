<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'ic_number' => $this->faker->numerify('############'), // 12digits
            'birthday' => $this->faker->date(),
            'contact_number' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'address' => $this->faker->address,
            'points' => $this->faker->randomFloat(2, 0, 1000),
            'remarks' => $this->faker->sentence,
            'tags' => json_encode(['regular', 'new']),
            'member_level' => $this->faker->randomElement(['normal', 'silver', 'gold']),
        ];
    }
} 