<?php

namespace jeremykenedy\LaravelLogger\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use jeremykenedy\LaravelLogger\App\Models\Activity;

class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'description' => $this->faker->sentence(3),
            'details' => $this->faker->paragraph(),
            'userType' => $this->faker->randomElement(['Guest', 'Registered', 'Crawler']),
            'userId' => $this->faker->numberBetween(1, 100),
            'route' => $this->faker->url(),
            'ipAddress' => $this->faker->ipv4(),
            'userAgent' => $this->faker->userAgent(),
            'locale' => $this->faker->locale(),
            'referer' => $this->faker->optional()->url(),
            'methodType' => $this->faker->randomElement(['GET', 'POST', 'PUT', 'DELETE', 'PATCH']),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Create an activity for today
     */
    public function today(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
    }

    /**
     * Create an activity for yesterday
     */
    public function yesterday(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ];
        });
    }

    /**
     * Create an activity for last week
     */
    public function lastWeek(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => now()->subWeek(),
                'updated_at' => now()->subWeek(),
            ];
        });
    }

    /**
     * Create an activity for last month
     */
    public function lastMonth(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => now()->subMonth(),
                'updated_at' => now()->subMonth(),
            ];
        });
    }

    /**
     * Create an activity for last year
     */
    public function lastYear(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => now()->subYear(),
                'updated_at' => now()->subYear(),
            ];
        });
    }

    /**
     * Create a guest activity
     */
    public function guest(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'userType' => 'Guest',
                'userId' => null,
            ];
        });
    }

    /**
     * Create a registered user activity
     */
    public function registered(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'userType' => 'Registered',
                'userId' => $this->faker->numberBetween(1, 100),
            ];
        });
    }

    /**
     * Create a crawler activity
     */
    public function crawler(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'userType' => 'Crawler',
                'userId' => null,
                'userAgent' => $this->faker->randomElement([
                    'Googlebot/2.1',
                    'Bingbot/2.0',
                    'Slurp',
                    'DuckDuckBot/1.0',
                ]),
            ];
        });
    }

    /**
     * Create a login activity
     */
    public function login(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'description' => 'User logged in',
                'methodType' => 'POST',
                'route' => '/login',
            ];
        });
    }

    /**
     * Create a logout activity
     */
    public function logout(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'description' => 'User logged out',
                'methodType' => 'POST',
                'route' => '/logout',
            ];
        });
    }

    /**
     * Create a view activity
     */
    public function view(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'description' => 'User viewed page',
                'methodType' => 'GET',
                'route' => $this->faker->randomElement([
                    '/dashboard',
                    '/profile',
                    '/settings',
                    '/home',
                ]),
            ];
        });
    }

    /**
     * Create a create activity
     */
    public function create(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'description' => 'User created resource',
                'methodType' => 'POST',
                'route' => $this->faker->randomElement([
                    '/posts',
                    '/users',
                    '/products',
                    '/orders',
                ]),
            ];
        });
    }

    /**
     * Create an update activity
     */
    public function update(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'description' => 'User updated resource',
                'methodType' => 'PUT',
                'route' => $this->faker->randomElement([
                    '/posts/1',
                    '/users/1',
                    '/products/1',
                    '/orders/1',
                ]),
            ];
        });
    }

    /**
     * Create a delete activity
     */
    public function delete(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'description' => 'User deleted resource',
                'methodType' => 'DELETE',
                'route' => $this->faker->randomElement([
                    '/posts/1',
                    '/users/1',
                    '/products/1',
                    '/orders/1',
                ]),
            ];
        });
    }
}
