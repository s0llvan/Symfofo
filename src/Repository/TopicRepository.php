<?php

namespace App\Repository;

use App\Entity\Topic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
* @method Topic|null find($id, $lockMode = null, $lockVersion = null)
* @method Topic|null findOneBy(array $criteria, array $orderBy = null)
* @method Topic[]    findAll()
* @method Topic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
*/
class TopicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Topic::class);
    }

    public function add(Topic $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Topic $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    /**
    * @return QueryBuilder
    */
    private function findByCategoryQueryBuilder(int $categoryId){
        $queryBuilder = $this->createQueryBuilder('t')
        ->where('t.category = :id')
        ->setParameter('id', $categoryId);
        
        return $queryBuilder;
    }
    
    /**
    * @return Paginator
    */
    public function findByCategory(int $categoryId, int $page, int $pageSize = 10) {
        $firstResult = ($page - 1) * $pageSize;
        
        $queryBuilder = $this->findByCategoryQueryBuilder($categoryId);
        
        // Set the returned page
        $queryBuilder->setFirstResult($firstResult);
        $queryBuilder->setMaxResults($pageSize);
        
        // Generate the Query
        $query = $queryBuilder->getQuery();
        
        // Generate the Paginator
        $paginator = new Paginator($query, true);
        return $paginator;
    }
}
