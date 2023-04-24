<?php

namespace App;

use App\RandomPicker;
use Exception;

class MealCreator
{
    private $foodsByDiet;
    private $period;

    public function __construct($foodsByDiet, $period)
    {
        $this->foodsByDiet = $foodsByDiet;
        $this->period = $period;
    }

    public function create()
    {
        $foods = [];
        foreach ($this->foodsByDiet as $food) {
            $foods[] = [
                'id' => $food->getId(),
                'name' => $food->getName(),
                'description' => $food->getDescription(),
                'category' => $food->getCategory(),
                'diets' => $food->getDiets()
            ];
        }

        $daily_lunch = [];
        $daily_dinner = [];

        $numberOfDays = $this->getDaysFrom($this->period);

        $categories = self::groupBy($foods, function ($food) {
            return $food['category'];
        });

        if (!array_key_exists("cereal", $categories)  ||
            !array_key_exists("protein", $categories)  ||
            !array_key_exists("veggie", $categories)  ||
            !array_key_exists("fruit", $categories)  ||
            !array_key_exists("fat", $categories)
        ) {
            throw new Exception("All categories must have at least one food: maybe one of the chosen foods doesn't fit for the chosen diet");
        }

        $cereals = new RandomPicker($categories['cereal']);
        $proteins = new RandomPicker($categories['protein']);
        $veggies = new RandomPicker($categories['veggie']);
        $fruits = new RandomPicker($categories['fruit']);
        $fats = new RandomPicker($categories['fat']);

        for ($i = 1; $i <= $numberOfDays; $i++) {
            $daily_lunch = [
                "cereals" => $cereals->pick(),
                "proteins" => $proteins->pick(),
                "veggies" => $veggies->pick(),
                "fruits" => $fruits->pick(),
                "fats" => $fats->pick()
            ];

            $daily_dinner = [
                "cereals" => $cereals->pick(),
                "proteins" => $proteins->pick(),
                "veggies" => $veggies->pick(),
                "fruits" => $fruits->pick(),
                "fats" => $fats->pick()
            ];

            $meal = [
                "day" => $i,
                "lunch" => $daily_lunch,
                "dinner" => $daily_dinner
            ];

            $meals[] = $meal;
        }

        return $meals;
    }

    public static function groupBy($array, $function)
    {
        $dictionary = [];
        if ($array) {
            foreach ($array as $item) {
                $dictionary[$function($item)][] = $item;
            }
        }
        return $dictionary;
    }

    private function getDaysFrom($period)
    {
        switch ($period) {
            case 'weekly':
                return 7;
            case 'monthly':
                return 30;
            case 'daily':
            default:
                return 1;
        }
    }
}
