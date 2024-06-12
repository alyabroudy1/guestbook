<?php

namespace App\Repository;

use App\Entity\Film;
use App\Entity\Movie;
use App\Entity\MovieType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
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
    public function findMoviesByTitleLoose($movieTitle): array
    {

        $queryBuilder = $this->createQueryBuilder('m');
        $result = $queryBuilder
            ->andWhere($queryBuilder->expr()->like('m.title', ':title'))
            ->orWhere($queryBuilder->expr()->like('m.searchContext', ':title'))
            ->setParameter('title', '%' . strtolower($movieTitle) . '%')
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
    public function findByTitleAndType(?string $movieTitle, ?MovieType $movieType)
    {

        $queryBuilder = $this->createQueryBuilder('m');

        return $queryBuilder
            ->andWhere('m INSTANCE OF :movieType') // Use INSTANCE OF for type comparison
            ->setParameter('movieType', $movieType)
            ->andWhere($queryBuilder->expr()->like('m.title', ':title'))
            ->setParameter('title', '%' . $movieTitle . '%')
            ->getQuery()
            ->getResult();
    }

    public function findSubMovies(Movie $movie)
    {
        $queryBuilder = $this->createQueryBuilder('m');
        return $queryBuilder
            ->andWhere('m.mainMovie = :mainMovie')
            ->setParameter('mainMovie', $movie->getId())
            ->getQuery()
            ->getResult()
            ;
    }

    public function findLastThirtyMovies()
    {
        $result = $this->createQueryBuilder('m')
            ->orderBy('m.id', 'DESC') // Order by ID descending (less ideal)
            ->setMaxResults(30)
        ->getQuery()
        ->getResult();

        return $result;
    }
}
