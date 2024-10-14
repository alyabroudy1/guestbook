<?php

namespace App\Repository;

use App\Entity\IptvChannel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IptvChannel>
 */
class IptvChannelRepository extends ServiceEntityRepository
{
    private array $favoriteGroups = [
        'Shahid',
        'NETFLIX',
        'OSN',
        'MBC',
        'BEIN',
        'ART',
        'MAJESTIC'
    ];
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IptvChannel::class);
    }

    /**
     * @return IptvChannel[] Returns an array of IptvChannel objects
     */
    public function search($query): array
    {
        // Add '%' before and after the query string for flexible matching
        $query = '%' . $query . '%';

        return $this->createQueryBuilder('i')
            ->andWhere('i.title LIKE :val')
            ->orWhere('i.tvgName LIKE :val')
            ->orWhere('i.groupTitle LIKE :val')
            ->setParameter('val', $query) // Use the updated query with wildcards
            ->orderBy('i.id', 'ASC')
            // ->setMaxResults(10) // Uncomment if you want to limit results
            ->getQuery()
            ->getResult();
    }

    public function getHomepageResults(): array
    {
        $result = [];
        // Add '%' before and after the query string for flexible matching
        foreach ($this->favoriteGroups as $category) {
            $query = '%' . $category . '%';
            $resultList['category'] = $category;

            $resultList['result'] = $this->createQueryBuilder('i')
//                ->andWhere('i.title LIKE :val')
//                ->orWhere('i.tvgName LIKE :val')
                ->orWhere('i.groupTitle LIKE :val')
                ->setParameter('val', $query) // Use the updated query with wildcards
                ->orderBy('i.id', 'ASC')
                // ->setMaxResults(10) // Uncomment if you want to limit results
                ->getQuery()
                ->getResult();
            $result[] = $resultList;
        }
        return $result;

    }

    public function findChannelsWithCredentialUrl()
    {
        return $this->createQueryBuilder('c')
            ->where('c.fileName IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    //    public function findOneBySomeField($value): ?IptvChannel
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
