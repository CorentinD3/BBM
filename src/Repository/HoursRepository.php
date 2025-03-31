<?php

namespace App\Repository;

use App\Entity\Hours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hours>
 *
 * @method Hours|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hours|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hours[]    findAll()
 * @method Hours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hours::class);
    }

    public function add(Hours $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Hours $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findPastReservations(\DateTime $now)
    {
        $qb = $this->createQueryBuilder('h')
            ->innerJoin('h.date', 'd')
            ->where('(d.date < :currentDate) OR (d.date = :currentDate AND h.hour < :currentTime)')
            ->andWhere('h.user IS NOT NULL')
            ->setParameter('currentDate', $now->format('Y-m-d')) // Date actuelle
            ->setParameter('currentTime', $now->format('H:i:s')) // Heure actuelle
            ->getQuery();

        // Debug : afficher la requête et les paramètres
        dump($qb->getSQL());
        dump($qb->getParameters());

        return $qb->getResult();
    }

    public function findAppointmentsForDate(\DateTime $date): array
    {
        return $this->createQueryBuilder('h')
            ->innerJoin('h.date', 'd')
            ->where('d.date = :date')
            ->andWhere('h.user IS NOT NULL')
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Hours[] Returns an array of Hours objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Hours
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
