<?php

namespace App;

use App\Entity\Food;

class Importer
{

    public function __construct(){}

    public function getFoods()
    {
        $json = file_get_contents(__DIR__ . "/../Food.json");
        $json_data = json_decode($json, true);

        $foodb_foods = $json_data['foods'];

        $foods = [];
        foreach($foodb_foods as $foodb) {
            if (!$this->isFoodGroupToDiscard($foodb)) {
                $food = new Food();
                $food->setName($foodb['name']);
                $food->setDescription($foodb['description']);
                $food->setCategory($this->assignCategory($foodb));
                $food->setDiets($this->assignDiets($foodb));

                $foods[] = $food;
            }
        }

        return $foods;
    }


    private function isFoodGroupToDiscard($foodb)
    {

        $foodGroups = [
            "Herbs and Spices", "Herbs and Spices", "Teas",
            "Coffee and coffee products", "Cocoa and cocoa products",
            "Beverages", "Confectioneries", "Dishes", "Snack foods", "Baby foods",
            "Unclassified", "Herbs and spices", null
        ];
        return in_array($foodb['food_group'], $foodGroups) || $foodb['name'] == $foodb['food_group'];
    }


    private function assignCategory($foodb)
    {

        if ($foodb['food_group'] == "Aquatic foods" && $foodb['food_subgroup'] == "Seaweed") {
            return "veggie";
        }

        switch ($foodb['food_group']) {
            case "Cereals and cereal products":
            case "Baking goods":
                return "cereal";

            case "Pulses":
            case "Soy":
            case "Aquatic foods":
            case "Animal foods":
            case "Milk and milk products":
            case "Eggs":
                return "protein";

            case "Vegetables":
            case "Gourds":
                return "veggie";

            case "Fruits":
                return "fruit";

            case "Nuts":
            case "Fats and oils":
                return "fat";

            default:
                return "No Category";
        }
    }

    
    private function assignDiets($foodb)
    {

        if ($foodb['food_group'] == "Aquatic foods" && $foodb['food_subgroup'] == "Seaweed") {
            return "onnivore, vegetarian, vegan";
        }

        switch ($foodb['food_group']) {
            case "Cereals and cereal products":
            case "Baking goods":
            case "Pulses":
            case "Soy":
            case "Vegetables":
            case "Gourds":
            case "Fruits":
            case "Nuts":
                return "onnivore, vegetarian, vegan";

            case "Aquatic foods":
            case "Animal foods":
                return "onnivore";

            case "Milk and milk products":
            case "Eggs":
                return "onnivore, vegetarian";

            case "Fats and oils":
                if ($foodb['food_subgroup'] == "Vegetable fats") {
                    return "onnivore, vegetarian, vegan";
                } else {
                    return "onnivore";
                }

            default:
                return "No Diet";
        }
    }
}
