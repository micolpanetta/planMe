<?php

namespace App\Tests\Controller;

use Zenstruck\Console\Test\InteractsWithConsole;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MealControllerTest extends WebTestCase
{

    use InteractsWithConsole;
    private $client;
    
    protected function setUp(): void
    {
        $client = static::createClient();
        $this->client = $client;

        $this->executeConsoleCommand("import-foods");
    }


    public function testCreateMealNoFoodAssociatedToUser(): void
    {
        $this->client->request('GET', '/api/v1/meal/vegan/daily/1');

        $response = $this->client->getResponse();

        $dec_response = json_decode($response->getContent());

        $this->assertEquals("No food associated to user 1", $dec_response->error_message);
        $this->assertEquals(404, $response->getStatusCode());
    }


    public function testFailCreateMealNotEnoughCategories(): void
    {
        //get all foods
        $this->client->request('GET', '/api/v1/food');
        $response = $this->client->getResponse();
        $foods = json_decode($response->getContent());

        //associate one food to user_id 1
        $body = [
            "food_ids" => [$foods[0]->id],
            "user_id" => 1
        ];
        $this->client->jsonRequest('POST', '/api/v1/food/user', $body);
        $insertResponse = $this->client->getResponse();
        $this->assertEquals(200, $insertResponse->getStatusCode());

        //generate meals with the only food associated
        $this->client->request('GET', '/api/v1/meal/vegan/daily/1');
        $response = $this->client->getResponse();
        $dec_response = json_decode($response->getContent());
        $this->assertEquals("All categories must have at least one food: maybe one of the chosen foods doesn't fit for the chosen diet", $dec_response->error_message);
        $this->assertEquals(400, $response->getStatusCode());
    }


    public function testFailCreateMealWrongFoodForDiet(): void
    {
        //get foods grouped by category
        $this->client->request('GET', '/api/v1/food/categories');
        $response = $this->client->getResponse();
        $foods = json_decode($response->getContent());

        //get a food per category, without any vegan protein 
        $proteins = $foods->protein;
        foreach ($proteins as $protein) {
            if (!str_contains($protein->diets, "vegan")) {
                $chosenProtein = $protein;
            }
        }
        $chosenCereal = $foods->cereal[0];
        $chosenVeggie = $foods->veggie[0];
        $chosenFat = $foods->fat[0];
        $chosenFruit = $foods->fruit[0];

        //associate chosen food, one per category, to user_id 1
        $body = [
            "food_ids" => [$chosenProtein->id, $chosenCereal->id, $chosenVeggie->id, $chosenFat->id, $chosenFruit->id],
            "user_id" => 1
        ];
        $this->client->jsonRequest('POST', '/api/v1/food/user', $body);
        $insertResponse = $this->client->getResponse();
        $this->assertEquals(200, $insertResponse->getStatusCode());

        //generate meals with the protein empty because not suitable fot the chosen diet
        $this->client->request('GET', '/api/v1/meal/vegan/daily/1');
        $response = $this->client->getResponse();
        $dec_response = json_decode($response->getContent());
        $this->assertEquals("All categories must have at least one food: maybe one of the chosen foods doesn't fit for the chosen diet", $dec_response->error_message);
        $this->assertEquals(400, $response->getStatusCode());
    }


    public function testCreateMeal(): void
    {
        //get foods grouped by category
        $this->client->request('GET', '/api/v1/food/categories');
        $response = $this->client->getResponse();
        $foods = json_decode($response->getContent());

        //compose array of foods categories
        $proteins = $foods->protein;
        foreach ($proteins as $protein) {
            if (str_contains($protein->diets, "vegan")) {
                $chosenProteins[] = $protein;
            }
        }

        $cereals = $foods->cereal;
        foreach ($cereals as $cereal) {
            if (str_contains($cereal->diets, "vegan")) {
                $chosenCereals[] = $cereal;
            }
        }

        $veggies = $foods->veggie;
        foreach ($veggies as $veggie) {
            if (str_contains($veggie->diets, "vegan")) {
                $chosenVeggies[] = $veggie;
            }
        }

        $fats = $foods->fat;
        foreach ($fats as $fat) {
            if (str_contains($fat->diets, "vegan")) {
                $chosenFats[] = $fat;
            }
        }

        $fruits = $foods->fruit;
        foreach ($fruits as $fruit) {
            if (str_contains($fruit->diets, "vegan")) {
                $chosenFruits[] = $fruit;
            }
        }

        //associate all foods to user_id 1
        for ($i = 0; $i < 10; $i++) {
            $body = [
                "food_ids" => [$chosenProteins[$i]->id, $chosenCereals[$i]->id, $chosenVeggies[$i]->id, $chosenFats[$i]->id, $chosenFruits[$i]->id],
                "user_id" => 1
            ];
            $this->client->jsonRequest('POST', '/api/v1/food/user', $body);
        }

        //generate daily meals for a vegan diet for user_id 1
        $this->client->request('GET', '/api/v1/meal/vegan/daily/1');
        $response = $this->client->getResponse();
        $dec_response = json_decode($response->getContent());
        $this->assertEquals(1, count($dec_response));
        $this->assertDietForDays($dec_response, 'vegan');
        $this->assertEquals(200, $response->getStatusCode());

        //generate weekly meals for a vegan diet for user_id 1
        $this->client->request('GET', '/api/v1/meal/vegan/weekly/1');
        $response = $this->client->getResponse();
        $dec_response = json_decode($response->getContent());
        $this->assertEquals(7, count($dec_response));
        $this->assertDietForDays($dec_response, 'vegan');
        $this->assertEquals(200, $response->getStatusCode());

        //generate monthly meals for a vegan diet for user_id 1
        $this->client->request('GET', '/api/v1/meal/vegan/monthly/1');
        $response = $this->client->getResponse();
        $dec_response = json_decode($response->getContent());
        $this->assertEquals(30, count($dec_response));
        $this->assertDietForDays($dec_response, 'vegan');
        $this->assertEquals(200, $response->getStatusCode());
    }


    private function assertDietForDays($days, $diet)
    {
        foreach ($days as $day) {
            $this->assertStringContainsString($diet, $day->lunch->cereals->diets);
            $this->assertStringContainsString($diet, $day->lunch->proteins->diets);
            $this->assertStringContainsString($diet, $day->lunch->veggies->diets);
            $this->assertStringContainsString($diet, $day->lunch->fats->diets);
            $this->assertStringContainsString($diet, $day->lunch->fruits->diets);
            $this->assertStringContainsString($diet, $day->dinner->cereals->diets);
            $this->assertStringContainsString($diet, $day->dinner->proteins->diets);
            $this->assertStringContainsString($diet, $day->dinner->veggies->diets);
            $this->assertStringContainsString($diet, $day->dinner->fats->diets);
            $this->assertStringContainsString($diet, $day->dinner->fruits->diets);
        }
    }


    public function testCreateMealCheckRepetition(): void
    {
        //get foods grouped by category
        $this->client->request('GET', '/api/v1/food/categories');
        $response = $this->client->getResponse();
        $foods = json_decode($response->getContent());

         //associate foods to user_id 1
        $proteins = $foods->protein;
        $cereals = $foods->cereal;
        $veggies = $foods->veggie;
        $fats = $foods->fat;
        for ($i = 0; $i < 10; $i++) {
            $body = [
                "food_ids" => [$proteins[$i]->id, $cereals[$i]->id, $veggies[$i]->id, $fats[$i]->id],
                "user_id" => 1
            ];
            $this->client->jsonRequest('POST', '/api/v1/food/user', $body);
        }

        //chose only 3 foods for the fruit category
        $fruits = $foods->fruit;
        $body = [
            "food_ids" => [$fruits[0]->id, $fruits[1]->id, $fruits[2]->id],
            "user_id" => 1
        ];
        //associate fruits to user_id 1
        $this->client->jsonRequest('POST', '/api/v1/food/user', $body);

        //generate monthly meals for a onnivore diet for user_id 1
        $this->client->request('GET', '/api/v1/meal/onnivore/monthly/1');
        $response = $this->client->getResponse();
        $dec_response = json_decode($response->getContent());

        //get all repeated fruits meal by meal
        $allFruits = [];
        foreach ($dec_response as $day) {
            $allFruits[] = $day->lunch->fruits->id;
            $allFruits[] = $day->dinner->fruits->id;
        }

        //check that 2 equal fruits never repeat
        for ($i = 0; $i < count($allFruits) - 1; $i++) {
            $this->assertNotEquals($allFruits[$i], $allFruits[$i + 1]);
        }

        $this->assertEquals(200, $response->getStatusCode());
    }


    public function testCreateMealCheckTotalRepetition(): void
    {
        //get foods grouped by category
        $this->client->request('GET', '/api/v1/food/categories');
        $response = $this->client->getResponse();
        $foods = json_decode($response->getContent());

        $proteins = $foods->protein;
        $cereals = $foods->cereal;
        $veggies = $foods->veggie;
        $fats = $foods->fat;

        //associate foods to user_id 1
        for ($i = 0; $i < 10; $i++) {
            $body = [
                "food_ids" => [$proteins[$i]->id, $cereals[$i]->id, $veggies[$i]->id, $fats[$i]->id],
                "user_id" => 1
            ];
            $this->client->jsonRequest('POST', '/api/v1/food/user', $body);
        }

        //associate 3 fruits to user_id 1
        $fruits = $foods->fruit;
        $body = [
            "food_ids" => [$fruits[0]->id, $fruits[1]->id, $fruits[2]->id],
            "user_id" => 1
        ];
        $this->client->jsonRequest('POST', '/api/v1/food/user', $body);

        //generate weekly meals for a onnivore diet for user_id 1
        $this->client->request('GET', '/api/v1/meal/onnivore/weekly/1');
        $response = $this->client->getResponse();
        $dec_response = json_decode($response->getContent());

         //get all repeated fruits meal by meal
        $allFruits = [];
        foreach ($dec_response as $day) {
            $allFruits[] = $day->lunch->fruits->id;
            $allFruits[] = $day->dinner->fruits->id;
        }

        //split the fruits array in chunks of three
        $chunkedFruitsId = array_chunk($allFruits, 3);

        //check three to three that the fruits never repeat
        for ($i = 0; $i < count($chunkedFruitsId) - 2; $i++) {
            $this->assertContains($fruits[0]->id, $chunkedFruitsId[$i]);
            $this->assertContains($fruits[1]->id, $chunkedFruitsId[$i]);
            $this->assertContains($fruits[2]->id, $chunkedFruitsId[$i]);
        }

        $this->assertEquals(200, $response->getStatusCode());
    }
}