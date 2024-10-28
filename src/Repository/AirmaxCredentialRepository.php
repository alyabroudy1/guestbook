<?php

namespace App\Repository;

use App\Entity\AirmaxCredential;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AirmaxCredential>
 */
class AirmaxCredentialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AirmaxCredential::class);
    }

    //    /**
    //     * @return AirmaxCredential[] Returns an array of AirmaxCredential objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    public function updateCredentials($credentialsUrl){
        $qb = $this->createQueryBuilder('c')
            ->update()
            ->set('c.credentialUrl', ':newUrl')
            ->where('c.domain = :domain')
            ->setParameter('newUrl', $credentialsUrl)
            ->setParameter('domain', 'airmax');

        $updatedRows = $qb->getQuery()->execute();

        // If no rows were updated, create a new Credential entity
        if ($updatedRows === 0) {
            $credential = new AirmaxCredential();
            $credential->setDomain('airmax');
            $credential->setCredentialUrl($credentialsUrl);

            $this->getEntityManager()->persist($credential);
            $this->getEntityManager()->flush();
        }

        return $updatedRows > 0;
    }

    //    public function findOneBySomeField($value): ?AirmaxCredential
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
