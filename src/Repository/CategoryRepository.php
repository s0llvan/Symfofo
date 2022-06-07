<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
    * @return QueryBuilder
    */
    private function findByParentCategoriesQueryBuilder(){
        $queryBuilder = $this->createQueryBuilder('c')
        ->where('c.parent is NULL');
        return $queryBuilder;
    }
    
    /**
    * @return Paginator
    */
    public function findParentCategories(int $page, int $pageSize = 10) {
        $firstResult = ($page - 1) * $pageSize;
        
        $queryBuilder = $this->findByParentCategoriesQueryBuilder();
        
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
