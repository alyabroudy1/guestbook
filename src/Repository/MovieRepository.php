<?php

namespace App\Repository;

use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Movie>
 *
 * @method Movie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movie[]    findAll()
 * @method Movie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    /**
         * @return Movie[] Returns an array of Movie objects
         */
    public function findMainMoviesByTitleLoose($movieTitle): array
    {

        $queryBuilder = $this->createQueryBuilder('m');
        $result = $queryBuilder
            ->andWhere('m.mainMovie IS NULL')
            ->andWhere($queryBuilder->expr()->like('m.title', ':title'))
            ->setParameter('title', '%' . $movieTitle . '%')
            ->getQuery()
        ->getResult();
return $result;
    }

//    /**
//     * @return Movie[] Returns an array of Movie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Movie
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findByTitleAndState(?string $movieTitle, ?int $state)
    {
        $queryBuilder = $this->createQueryBuilder('m');
        return $queryBuilder
            ->andWhere('m.state = :state')
            ->setParameter('state', $state)
            ->andWhere($queryBuilder->expr()->like('m.title', ':title'))
            ->setParameter('title', '%' . $movieTitle . '%')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findSubMovies(Movie $movie)
    {
        $queryBuilder = $this->createQueryBuilder('m');
        return $queryBuilder
            ->andWhere('m.mainMovie = :mainMovie')
            ->setParameter('mainMovie', $movie)
            ->getQuery()
            ->getResult()
            ;
    }
}
