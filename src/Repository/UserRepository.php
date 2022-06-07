<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
    * @return QueryBuilder
    */
    private function findByUsersQueryBuilder(){
        $queryBuilder = $this->createQueryBuilder('u');
        return $queryBuilder;
    }
    
    /**
    * @return Paginator
    */
    public function findUsers(int $page, int $pageSize = 10) {
        $firstResult = ($page - 1) * $pageSize;
        
        $queryBuilder = $this->findByUsersQueryBuilder();
        
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
