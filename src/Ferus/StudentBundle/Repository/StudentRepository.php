<?php

namespace Ferus\StudentBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Ferus\StudentBundle\Entity\Student;

/**
 * StudentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StudentRepository extends EntityRepository
{
    public function findOneById($id)
    {
        return $this->createQueryBuilder('s')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();
    }

    public function queryAll()
    {
        return $this->createQueryBuilder('s')
            ->getQuery();
    }

    public function search($query = null, $maxResult = 50)
    {
        return $this->querySearch($query)->setMaxResults($maxResult)->getResult();
    }

    public function querySearch($query = null)
    {
        $qb = $this->createQueryBuilder('s');
        if($query == null) return $qb->getQuery();

        $query = trim($query);
        $words = explode(' ', $query);

        if(count($words) === 1){
            if(preg_match('/^[a-z\.-]+@edu\.esiee\.fr$/', $query))
                $qb
                    ->where('s.email = :email')
                    ->setParameter('email', $query);
            elseif(preg_match('/^[0-9]+$/', $query))
                $qb
                    ->where('s.id = :id')
                    ->setParameter('id', $query);
            else
                $qb
                    ->where('s.firstName LIKE :query')
                    ->orWhere('s.lastName LIKE :query')
                    ->setParameter('query', "%$query%");
        }
        else{
            foreach($words as $key => $word){
                $qb
                    ->andWhere("s.firstName LIKE :query$key OR s.lastName LIKE :query$key")
                    ->setParameter("query$key", "%$word%");
            }
        }

        return $qb->getQuery();
    }

    public function remove(Student $student)
    {
        $this->createQueryBuilder('s')
            ->delete()
            ->where('s = :student')
            ->setParameter('student', $student)
            ->getQuery()
            ->execute();
    }

    /**
     * @param Student $student
     * @return Student|null
     */
    public function findSoftDeleted(Student $student)
    {
        $query = $this->createQueryBuilder('s')
            ->where('s.deletedAt IS NOT NULL')
            ->andWhere('s = :student')
            ->setParameter('student', $student)
            ->getQuery()
        ;

        try{
            return $query->getSingleResult();
        }
        catch(NoResultException $e){
            return null;
        }
    }

    public function isIdAvailable($id)
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s)')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult()
            == 0;
    }

    public function getStats()
    {
        return $this->createQueryBuilder('s')
            ->select('s.class class, COUNT(s) total, SUM(s.isContributor) contributors')
            ->where('s.class IS NOT NULL')
            ->andWhere('s.class != \'\'')
            ->groupBy('s.class')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
    }

    public function findFromToken($token)
    {
        $data = explode('/', preg_replace('#^[a-z][0-9][a-z]([0-9]+)[a-z]([0-9a-zA-Z]+)[a-z]([0-9]+)[a-z]$#', '$1/$2/$3', $token));

        $s = $this->findOneBy(array('id'=>$data[0]));
        return $s == null ? $s : $s->getHash() != $data[1] ? null : $s;
    }

    public function queryKfet()
    {
        return $this->createQueryBuilder('s')
            ->where('s.deletedAt IS NULL')
            ->andWhere("s.class NOT IN('CESURE', 'E3FIC', 'E3FRC', 'E4FIC', 'E4FRC', 'E5FIC', 'E6', 'E6FI', 'E6FR', 'PROJET_ETU')")
            ->getQuery()
            ->execute();
    }
}
