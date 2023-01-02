<?php

namespace App\Repository;

use App\Entity\RealmPositionPermission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RealmPositionPermission>
 *
 * @method RealmPositionPermission|null find($id, $lockMode = null, $lockVersion = null)
 * @method RealmPositionPermission|null findOneBy(array $criteria, array $orderBy = null)
 * @method RealmPositionPermission[]    findAll()
 * @method RealmPositionPermission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RealmPositionPermissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RealmPositionPermission::class);
    }

    public function save(RealmPositionPermission $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RealmPositionPermission $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return RealmPositionPermission[] Returns an array of RealmPositionPermission objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RealmPositionPermission
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
