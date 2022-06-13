<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function add(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
    * @return QueryBuilder
    */
    private function findByTopicQueryBuilder(int $topicId){
        $queryBuilder = $this->createQueryBuilder('p')
        ->where('p.topic = :id')
        ->setParameter('id', $topicId);
        
        return $queryBuilder;
    }
    
    /**
    * @return Paginator
    */
    public function findByTopic(int $topicId, int $page, int $pageSize = 10) {
        $firstResult = ($page - 1) * $pageSize;
        
        $queryBuilder = $this->findByTopicQueryBuilder($topicId);
        
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
