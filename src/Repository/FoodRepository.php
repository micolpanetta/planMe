<?php

namespace App\Repository;

use App\Entity\Food;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Food>
 *
 * @method Food|null find($id, $lockMode = null, $lockVersion = null)
 * @method Food|null findOneBy(array $criteria, array $orderBy = null)
 * @method Food[]    findAll()
 * @method Food[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FoodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Food::class);
    }

    public function save(Food $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Food $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByDiet($value): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.diets LIKE :val')
            ->setParameter('val', '%' . $value . '%')
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(1000)
            ->getQuery()
            ->getResult();
    }

    public function findByDietFilterByUser($diet, $userId): array
    {
        return $this->createQueryBuilder('f')
            ->join('f.user', 'uf')
            ->andWhere('uf.user_id = :userId')
            ->andWhere('f.diets LIKE :diet')
            ->setParameter('userId', $userId)
            ->setParameter('diet', '%' . $diet . '%')
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(1000)
            ->getQuery()
            ->getResult();
    }

    public function findByCategory($value): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.category LIKE :val')
            ->setParameter('val', '%' . $value . '%')
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(1000)
            ->getQuery()
            ->getResult();
    }

    public function findByName($value): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.name LIKE :val')
            ->setParameter('val', '%' . $value . '%')
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(1000)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Food[] Returns an array of Food objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Food
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
