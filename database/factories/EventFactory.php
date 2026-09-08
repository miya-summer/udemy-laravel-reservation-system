<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $availableHour = $this->faker->numberBetween(10, 18);   //10時～18時
        $minutes = [0, 30];                                     // 00分か 30分
        $mKey = array_rand($minutes);                           //ランダムにキーを取得
        $addHour = $this->faker->numberBetween(1, 3);           // イベント時間 1時間～3時間

        $dummyDate = $this->faker->dateTimeThisMonth;
        $startDate = $dummyDate->setTime($availableHour, $minutes[$mKey]);
        $clone = clone $startDate;                              // そのままmodifyするとstartDateも変わるためコピー
        $endDate = $clone->modify('+'.$addHour.'hour');

        return [
            'name' => $this->faker->name,
            'information' => $this->faker->realText,
            'max_people' => $this->faker->numberBetween(1,20),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_visible' => $this->faker->boolean
        ];
    }
}
