<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }
    public function findAllByEvent(int $id) : array {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c.content')
            ->addSelect('c.note')
            ->addSelect('c.id AS com_id')
            ->addSelect('e.id AS e_id')
            ->addSelect('c.createdAt')
            ->addSelect('c.updatedAt')
            ->addSelect('u.nom AS u_nom')
            ->addSelect('u.prenom AS u_prenom')
            ->addSelect('u.imageEmp as u_image')
            ->addSelect('u.id AS u_id')
            ->innerJoin('c.etudiant','u')
            ->innerJoin('c.event','e')
            ->where('e.id = :id')
            ->setParameter('id',$id)
        ;
        return $qb->getQuery()->getResult();

    }
    // /**
    //  * @return Comment[] Returns an array of Comment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
