<?php

namespace DirectokiBundle\Repository;

use DirectokiBundle\Entity\Data;
use DirectokiBundle\Entity\Directory;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordRepository extends EntityRepository {



    public function doesPublicIdExist($id, Directory $directory)
    {
        if ($directory->getId()) {
            $s =  $this->getEntityManager()
                       ->createQuery(
                           ' SELECT r FROM DirectokiBundle:Record r'.
                           ' WHERE r.directory = :directory AND r.publicId = :public_id'
                       )
                       ->setParameter('directory', $directory)
                       ->setParameter('public_id', $id)
                       ->getResult();
            return (boolean)$s;
        } else {
            return false;
        }
    }


    public function getRecordsNeedingAttention(Directory $directory) {

        return $this->getEntityManager()
            ->createQuery(
                ' SELECT r FROM DirectokiBundle:Record r'.
                ' LEFT JOIN r.recordHasFieldStringValues rhfsv WITH rhfsv.refusedAt IS NULL AND rhfsv.approvedAt IS NULL '.
                ' LEFT JOIN r.recordHasFieldTextValues rhftv WITH rhftv.refusedAt IS NULL AND rhftv.approvedAt IS NULL '.
                // TODO LatLng
                ' LEFT JOIN r.recordHasFieldBooleanValues rhfbv WITH rhfbv.refusedAt IS NULL AND rhfbv.approvedAt IS NULL '.
                // TODO State
                // TODO Report
                ' WHERE r.directory = :directory AND '.
                '(rhfsv.id IS NOT NULL OR rhftv.id IS NOT NULL OR rhfbv.id IS NOT NULL)'.
                'GROUP BY r.id '
            )
            ->setMaxResults(50)
            ->setParameter('directory', $directory)
            ->getResult();
    }


}
