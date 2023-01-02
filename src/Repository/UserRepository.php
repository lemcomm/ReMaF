<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserLoaderInterface {
        # This exists for when we need to rehash user passwords, for instance, when changing algorthms.
        # We could also use to implement more complex EntityRepository->findByRandomThings() functions. They'd go here, as public function findByRandomThings().
        public function __construct(ManagerRegistry $registry) {
                parent::__construct($registry, User::class);
        }

        public function save(User $entity, bool $flush = false): void {
                $this->getEntityManager()->persist($entity);

                if ($flush) {
                        $this->getEntityManager()->flush();
                }
        }

        public function remove(User $entity, bool $flush = false): void {
                $this->getEntityManager()->remove($entity);

                if ($flush) {
                        $this->getEntityManager()->flush();
                }
        }

        public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void {
                if (!$user instanceof User) {
                        throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
                }

                $user->setPassword($newHashedPassword);
                $this->getEntityManager()->flush();
        }
}
