<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findByCriteria(array $criteria)
    {
        $qb = $this->createQueryBuilder('e');

        if (isset($criteria['titre'])) {
            $qb->andWhere('e.titre LIKE :titre')
               ->setParameter('titre', '%'.$criteria['titre'].'%');
        }

        if (isset($criteria['date'])) {
            $now = new \DateTime();
            if ($criteria['date'] === 'today') {
                $qb->andWhere('e.date = :today')
                   ->setParameter('today', $now->format('Y-m-d'));
            } elseif ($criteria['date'] === 'this_month') {
                $startOfMonth = $now->modify('first day of this month')->format('Y-m-d');
                $endOfMonth = $now->modify('last day of this month')->format('Y-m-d');
                $qb->andWhere('e.date BETWEEN :startOfMonth AND :endOfMonth')
                   ->setParameter('startOfMonth', $startOfMonth)
                   ->setParameter('endOfMonth', $endOfMonth);
            } elseif ($criteria['date'] === 'this_year') {
                $startOfYear = $now->modify('first day of January')->format('Y-m-d');
                $endOfYear = $now->modify('last day of December')->format('Y-m-d');
                $qb->andWhere('e.date BETWEEN :startOfYear AND :endOfYear')
                   ->setParameter('startOfYear', $startOfYear)
                   ->setParameter('endOfYear', $endOfYear);
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function findByCreator(User $user, array $criteria = []): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.creator = :user')
            ->setParameter('user', $user);

        if (!empty($criteria['titre'])) {
            $qb->andWhere('e.titre LIKE :titre')
                ->setParameter('titre', '%' . $criteria['titre'] . '%');
        }

        if (!empty($criteria['date'])) {
            $qb->andWhere('e.date = :date')
                ->setParameter('date', $criteria['date']);
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
