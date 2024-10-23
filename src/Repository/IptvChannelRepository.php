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
    private array $favoritePaidGroups = [
        'Shahid',
        'NETFLIX',
        'OSN',
        'MBC',
        'BEIN',
        'ART',
        'MAJESTIC'
    ];

    private array $favoriteGroups = [
        'Shahid',
        'أم بي سي',
        'NEWS الاخبار',
        'Children أطفال'
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
//            ->orWhere('i.group LIKE :val')
            ->setParameter('val', $query) // Use the updated query with wildcards
            ->orderBy('i.id', 'ASC')
            // ->setMaxResults(10) // Uncomment if you want to limit results
            ->getQuery()
            ->getResult();
    }

    public function getHomepageResults(bool $paidChannels): array
    {
        if ($paidChannels){
            return $this->getHomepageFavoritesChannels($this->favoritePaidGroups, true);
        }
        return $this->getHomepageFavoritesChannels($this->favoriteGroups, false);
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
    private function getHomepageFavoritesChannels($favoriteGroups, $paid): array
    {
        $result = [];
        $paidQueryValue = $paid ? 'IS NOT NULL ' : 'IS NULL';
        // Add '%' before and after the query string for flexible matching
        foreach ($favoriteGroups as $category) {
            $query = '%' . $category . '%';
            $resultList['category'] = $category;

            $resultList['result'] = $this->createQueryBuilder('i')
//                ->andWhere('i.title LIKE :val')
//                ->orWhere('i.tvgName LIKE :val')
                ->orWhere('i.fileName ' . $paidQueryValue)
                ->andWhere('i.groupTitle LIKE :val')
                ->setParameter('val', $query) // Use the updated query with wildcards
                ->orderBy('i.id', 'ASC')
                // ->setMaxResults(10) // Uncomment if you want to limit results
                ->getQuery()
                ->getResult();
            $result[] = $resultList;
        }
        return $result;
    }

    public function removeOldPaidList()
    {
        $qb = $this->createQueryBuilder('i');
        $qb->delete()
            ->where('i.fileName IS NOT NULL');

        $query = $qb->getQuery();
        $query->execute();
    }

    private function getHomepagePaidChannels()
    {
        $result = [];
        // Add '%' before and after the query string for flexible matching
        foreach ($this->favoriteGroups as $category) {
            $query = '%' . $category . '%';
            $resultList['title'] = $category;

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
}
