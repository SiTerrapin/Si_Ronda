<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Petugas>
 */
class PetugasFactory extends Factory
{
    public function definition(): array
    {
        return [
            'username'     => substr($this->faker->unique()->userName(), 0, 20),
            'password'     => Hash::make('user123'),
            'nama_lengkap' => substr($this->faker->name(), 0, 50),
            'no_hp'        => '08' . $this->faker->numerify('##########'),
            'alamat'       => 'RT 0' . $this->faker->numberBetween(1, 5) . ' / Blok ' . $this->faker->randomElement(['A', 'B', 'C']),
            'gender'       => $this->faker->randomElement(['M', 'F']),
            'role'         => 'petugas',
            'is_active'    => '1',
        ];
    }
}
