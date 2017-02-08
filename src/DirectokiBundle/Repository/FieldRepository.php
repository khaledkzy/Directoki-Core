<?php

namespace DirectokiBundle\Repository;

use DirectokiBundle\Entity\Directory;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldRepository extends EntityRepository {



    public function getNextFieldSortValue(Directory $directory)
    {

        $s =  $this->getEntityManager()
                   ->createQuery(
                       ' SELECT MAX(f.sort) AS sort FROM DirectokiBundle:Field f'.
                       ' WHERE f.directory = :directory '
                   )
                   ->setParameter('directory', $directory)
                   ->getScalarResult();

        return $s[0]['sort'] + 1;

    }

    public function findForDirectory(Directory $directory) {
        return $this->getEntityManager()
             ->createQuery(
                 ' SELECT f FROM DirectokiBundle:Field f'.
                 ' WHERE f.directory = :directory ORDER BY f.sort ASC  '
             )
             ->setParameter('directory', $directory)
             ->getResult();
    }

}
