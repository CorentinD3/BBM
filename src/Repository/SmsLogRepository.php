<?php

namespace App\Repository;

use App\Entity\SmsLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SmsLog>
 *
 * @method SmsLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method SmsLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method SmsLog[]    findAll()
 * @method SmsLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SmsLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SmsLog::class);
    }

    public function add(SmsLog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SmsLog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return SmsLog[] Returns an array of SmsLog objects
     */
    public function findSmsSentTodayByIp(string $ipAddress): array
    {
        $startOfDay = (new \DateTime())->setTime(0, 0, 0)->format('Y-m-d H:i:s'); // Conversion ici

        return $this->createQueryBuilder('s')
            ->andWhere('s.ipAddress = :ipAddress')
            ->andWhere('s.sentAt >= :startOfDay')
            ->setParameter('ipAddress', $ipAddress)
            ->setParameter('startOfDay', $startOfDay) // Conversion appliquée
            ->getQuery()
            ->getResult();
    }

    public function findSmsSentTodayByNumber(string $number): array
    {
        $startOfDay = (new \DateTime())->setTime(0, 0, 0)->format('Y-m-d H:i:s'); // Conversion correcte

        return $this->createQueryBuilder('s')
            ->andWhere('s.number = :number')
            ->andWhere('s.sentAt >= :startOfDay')
            ->setParameter('number', $number)
            ->setParameter('startOfDay', $startOfDay) // Conversion appliquée
            ->getQuery()
            ->getResult();
    }


//    /**
//     * @return SmsLog[] Returns an array of SmsLog objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SmsLog
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
