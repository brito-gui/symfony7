<?php

namespace App\Repository;

use App\Entity\SubCompany;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubCompany>
 *
 * @method SubCompany|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubCompany|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubCompany[]    findAll()
 * @method SubCompany[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubCompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubCompany::class);
    }

}
