<?php

namespace App\Repository;

use App\Entity\UserSubCompany;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSubCompany>
 *
 * @method UserSubCompany|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSubCompany|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSubCompany[]    findAll()
 * @method UserSubCompany[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSubCompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSubCompany::class);
    }

//    /**
//     * @return UserSubCompany[] Returns an array of UserSubCompany objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UserSubCompany
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
