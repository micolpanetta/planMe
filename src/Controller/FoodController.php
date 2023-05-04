<?php

namespace App\Controller;

use App\MealCreator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Food;
use App\Entity\UserFood;
use App\Importer;

/**
 * @Route("/api/v1", name="api_v1")
 */
class FoodController extends AbstractController
{
    /**
     * @Route("/food", name="food_index", methods={"GET"})
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $foods = $doctrine
            ->getRepository(Food::class)
            ->findAll();

        $data = [];

        foreach ($foods as $food) {
            $data[] = [
                'id' => $food->getId(),
                'name' => $food->getName(),
                'description' => $food->getDescription(),
                'category' => $food->getCategory(),
                'diets' => $food->getDiets()
            ];
        }

        return $this->json($data);
    }


    /**
     * @Route("/food/categories", name="food_search_cat", methods={"GET"})
     */
    public function searchByCategory(ManagerRegistry $doctrine): Response
    {
        $foods = $doctrine
            ->getRepository(Food::class)
            ->findAll();

        $data = [];

        foreach ($foods as $food) {
            $data[] = [
                'id' => $food->getId(),
                'name' => $food->getName(),
                'description' => $food->getDescription(),
                'category' => $food->getCategory(),
                'diets' => $food->getDiets()
            ];
        }

        $categories = MealCreator::groupBy($data, function ($data) {
            return $data['category'];
        });

        return $this->json($categories);
    }


    /**
     * @Route("/food/name/{name}", name="food_search_name", methods={"GET"})
     */
    public function searchByName(ManagerRegistry $doctrine, string $name): Response
    {
        $foods = $doctrine
            ->getRepository(Food::class)
            ->findByName($name);

        $data = [];

        foreach ($foods as $food) {
            $data[] = [
                'id' => $food->getId(),
                'name' => $food->getName(),
                'description' => $food->getDescription(),
                'category' => $food->getCategory(),
                'diets' => $food->getDiets()
            ];
        }

        return $this->json($data);
    }


    /**
     * @Route("/food", name="food_new", methods={"POST"})
     */
    public function new(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();

        $food = new Food();
        $food->setName($request->request->get('name'));
        $food->setDescription($request->request->get('description'));
        $food->setCategory($request->request->get('category'));
        $food->setDiets($request->request->get('diets'));

        $entityManager->persist($food);
        $entityManager->flush();

        return $this->json(["message" => 'Successfully created new food  with id ' . $food->getId()]);
    }


    /**
     * @Route("/food/user", name="food_user", methods={"POST"})
     */
    public function newUserFood(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();
        $foodIds = $request->request->all('food_ids');
        $foodRepository = $doctrine->getRepository(Food::class);
        
        foreach($foodIds as $foodId) {
            $userFood = new UserFood();
            $userFood->setUserId($request->request->get('user_id'));    
            $food = $foodRepository->find($foodId);
            $userFood->setFood($food);
            $entityManager->persist($userFood);
        }

        $entityManager->flush();
        return $this->json(["message" => 'Successfully associated new foods to user ' . $userFood->getUserId()]);
    }


    /**
     * @Route("/food/{id}", name="food_show", methods={"GET"})
     */
    public function show(ManagerRegistry $doctrine, int $id): Response
    {
        $food = $doctrine->getRepository(Food::class)->find($id);

        if (!$food) {
            return $this->json(["errorMessage" => 'No food found for id ' . $id], 404);
        }

        $data[] = [
            'id' => $food->getId(),
            'name' => $food->getName(),
            'description' => $food->getDescription(),
            'category' => $food->getCategory(),
            'diets' => $food->getDiets()
        ];

        return $this->json($data);
    }


    /**
     * @Route("/food/{id}", name="food_edit", methods={"PUT"})
     */
    public function edit(ManagerRegistry $doctrine, Request $request, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $food = $entityManager->getRepository(Food::class)->find($id);

        if (!$food) {
            return $this->json(["errorMessage" => 'No food found for id ' . $id], 404);
        }

        if ($request->get('name')) {
            $food->setName($request->request->get('name'));
        }

        if ($request->get('description')) {
            $food->setDescription($request->request->get('description'));
        }

        if ($request->get('category')) {
            $food->setCategory($request->request->get('category'));
        }

        if ($request->get('diets')) {
            $food->setDiets($request->request->get('diets'));
        }

        $entityManager->flush();

        $data[] = [
            'id' => $food->getId(),
            'name' => $food->getName(),
            'description' => $food->getDescription(),
            'category' => $food->getCategory(),
            'diets' => $food->getDiets()
        ];

        return $this->json($data);
    }


    /**
     * @Route("/food/{id}", name="food_delete", methods={"DELETE"})
     */
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $food = $entityManager->getRepository(Food::class)->find($id);

        if (!$food) {
            return $this->json(["errorMessage" => 'No food found for id ' . $id], 404);
        }

        $entityManager->remove($food);
        $entityManager->flush();

        return $this->json(["message" => 'Successfully deleted food  with id ' . $id]);
    }
}
