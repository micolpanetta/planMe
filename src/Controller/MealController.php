<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Food;
use App\MealCreator;

/**
 * @Route("/api/v1", name="api_v1")
 */
class MealController extends AbstractController
{

    /**
     * @Route("/meal/{diet}/{period}/{user_id}", name="meal_create", methods={"GET"})
     */
    public function generateMeal(ManagerRegistry $doctrine, string $diet, string $period, int $user_id): Response
    {
        $foodsByDiet = $doctrine
            ->getRepository(Food::class)
            ->findByDietFilterByUser($diet, $user_id);
  
           
        if(empty($foodsByDiet)) {
            $errorMessage = ["error_message" => "No food associated to user $user_id"]; 
            return $this->json($errorMessage)->setStatusCode(404);
        }    
       
        $mealCreator = new MealCreator($foodsByDiet, $period);
        
        try{
            $meals = $mealCreator->create();
        } catch(\Exception $e) {
            $errorMessage = ["error_message" => $e->getMessage()]; 
            return $this->json($errorMessage)->setStatusCode(400);
        }

        $date = date('YmdHis');

        file_put_contents("../diet_$date.json", json_encode($meals));

        return $this->json($meals);
    }
}
