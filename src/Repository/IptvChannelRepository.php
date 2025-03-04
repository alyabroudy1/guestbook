<?php

namespace App\Repository;

use App\Entity\IptvChannel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        'Syria سورية',
        'Children',
        '🇩🇪 Germany',
//        'SPORT رياضية',
//        'SPORT VIP',
    ];
    public function __construct(
        ManagerRegistry $registry,
        private EntityManagerInterface $entityManager
    )
    {
        parent::__construct($registry, IptvChannel::class);
    }

    /**
     * @return IptvChannel[] Returns an array of IptvChannel objects
     */
    public function search($query): array
    {
        // Add '%' before and after the query string for flexible matching
        $results['category'] = $query;
        $query = '%' . $query . '%';
        $results['result'] =  $this->createQueryBuilder('i')
            ->andWhere('i.title LIKE :val')
            ->orWhere('i.tvgName LIKE :val')
            ->orWhere('i.groupTitle LIKE :val')
//            ->orWhere('i.group LIKE :val')
            ->setParameter('val', $query) // Use the updated query with wildcards
            ->orderBy('i.id', 'ASC')
            // ->setMaxResults(10) // Uncomment if you want to limit results
            ->getQuery()
            ->getResult();
        return $results;
    }

    public function getHomepageResults(bool $paidChannels): array
    {
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
//        $result[]  = $this->getHomepagePaidFavoritesChannels();
        return $result;
    }


    private function getHomepagePaidFavoritesChannels()
    {

        $result['category'] = 'تجريبي';
        $tempList = [];
        // Add '%' before and after the query string for flexible matching
        foreach ($this->favoritePaidGroups as $category) {
            $query = '%' . $category . '%';

            $resultList = $this->createQueryBuilder('i')
//                ->andWhere('i.title LIKE :val')
//                ->orWhere('i.tvgName LIKE :val')
                ->orWhere('i.fileName IS NOT NULL')
                ->andWhere('i.groupTitle LIKE :val')
                ->setParameter('val', $query) // Use the updated query with wildcards
                ->orderBy('i.id', 'ASC')
                // ->setMaxResults(10) // Uncomment if you want to limit results
                ->getQuery()
                ->getResult();
            $tempList = array_merge($tempList, $resultList) ;
        }
        $result['result'] = $tempList;
        return $result;
    }

    /**
     * Removes old paid IPTV channels
     */
    public function removeOldPaidList(): void
    {
        try {

//            $qb = $this->createQueryBuilder('i');
//            $qb->delete()
//                ->where('i.fileName IS NOT NULL');
//
//            $query = $qb->getQuery();
//            $query->execute();
//



            $this->entityManager->beginTransaction();

            $qb = $this->createQueryBuilder('i');
            $qb->delete()
                ->where('i.groupTitle LIKE :paidPattern')
                ->setParameter('paidPattern', '%paid%') // Adjust this pattern as needed
                ->getQuery()
                ->execute();

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new \RuntimeException('Failed to remove old paid list: ' . $e->getMessage(), 0, $e);
        }
    }

    // Alternative version using TRUNCATE (faster for large datasets)
    /**
     * Truncates the IPTV channels table
     * Note: This method is database-specific and may need adjustment based on your DBMS
     *
     * @throws \RuntimeException If truncation fails
     */
    public function truncateAll(): void
    {
        try {
            $this->entityManager->beginTransaction();

            $connection = $this->entityManager->getConnection();
            $platform = $connection->getDatabasePlatform();
            $tableName = $this->getClassMetadata()->getTableName();

            // Handle different database platforms
            switch ($platform->getName()) {
                case 'mysql':
                case 'mariadb':
                    $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
                    $connection->executeStatement($platform->getTruncateTableSQL($tableName));
                    $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
                    break;

                case 'postgresql':
                    // For PostgreSQL, TRUNCATE with CASCADE to handle FKs
                    $connection->executeStatement("TRUNCATE TABLE {$tableName} CASCADE");
                    break;

                case 'sqlite':
                    // SQLite doesn't support disabling FK checks easily, use DELETE instead
                    $connection->executeStatement("DELETE FROM {$tableName}");
                    break;

                default:
                    // Fallback to DELETE for unsupported platforms
                    $connection->executeStatement("DELETE FROM {$tableName}");
                    break;
            }

            $this->entityManager->commit();
            $this->entityManager->clear(IptvChannel::class);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new \RuntimeException('Failed to truncate IPTV channels: ' . $e->getMessage(), 0, $e);
        }
    }


    /**
     * Save single channel (for compatibility if needed)
     */
    public function save(IptvChannel $channel, bool $flush = true): void
    {
        if (empty($channel)) {
            return;
        }

        try {
            $this->entityManager->beginTransaction();

                // Check for existing channel by URL to avoid duplicates
                $existing = $this->findOneBy(['url' => $channel->getUrl()]);

                if ($existing) {
                    // Update existing record
                    $existing->setTvgName($channel->getTvgName())
                        ->setGroupTitle($channel->getGroupTitle())
                        ->setTvgLogo($channel->getTvgLogo());
                    $this->entityManager->persist($existing);
                } else {
                    // Persist new channel
                    $this->entityManager->persist($channel);
                }

            if ($flush) {
                $this->entityManager->flush();
                // Clear the entity manager to free memory
                $this->entityManager->clear(IptvChannel::class);
            }

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new \RuntimeException('Failed to save IPTV channel batch: ' . $e->getMessage(), 0, $e);
//            dd($e->getMessage());
        }

    }

    /**
     * Save a batch of IPTV channels efficiently
     */
    public function saveBatch(array $channels, bool $flush = true): void
    {
        if (empty($channels)) {
            return;
        }

        try {
            $this->entityManager->beginTransaction();

            foreach ($channels as $channel) {
                $existing = $this->findOneBy(['url' => $channel->getUrl()]);

                if ($existing) {
                    $existing->setTvgName($channel->getTvgName())
                        ->setGroupTitle($channel->getGroupTitle())
                        ->setTvgLogo($channel->getTvgLogo());
                    $this->entityManager->persist($existing);
//                    $this->entityManager->detach($existing); // Free memory
                } else {
                    $this->entityManager->persist($channel);
                }
            }

            if ($flush) {
                $this->entityManager->flush();
                $this->entityManager->clear(); // Clear all entities
            }

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new \RuntimeException('Failed to save IPTV channel batch: ' . $e->getMessage(), 0, $e);
        }
    }
}
