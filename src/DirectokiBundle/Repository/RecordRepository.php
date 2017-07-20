<?php

namespace DirectokiBundle\Repository;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\RecordsInDirectoryQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordRepository extends EntityRepository {


    public function findByRecordsInDirectoryQuery(RecordsInDirectoryQuery $recordInDirectoryQuery) {
        $where = array(
            'r.directory = :directory '
        );
        $joins = array();
        $params = array(
            'directory'=>$recordInDirectoryQuery->getDirectory(),
        );

        if ($recordInDirectoryQuery->isPublishedOnly()) {
            $where[] = 'r.cachedState = :cachedState';
            $params['cachedState'] = RecordHasState::STATE_PUBLISHED;
        }

        if ($recordInDirectoryQuery->getFullTextSearch() && $recordInDirectoryQuery->getLocale()) {
            $joins[] = " JOIN r.recordLocaleCaches rlc WITH rlc.locale = :locale ";
            $params['locale'] = $recordInDirectoryQuery->getLocale();
            $where[] = ' rlc.fullTextSearch LIKE :fullTextSearch';
            $params['fullTextSearch'] = '%'.mb_strtolower($recordInDirectoryQuery->getFullTextSearch()).'%';
        }

        $query =  $this->getEntityManager()
            ->createQuery(
                ' SELECT r FROM DirectokiBundle:Record r '.
                implode(' ', $joins).
                ' WHERE '. implode(' AND ', $where)
            );;

        foreach($params as $k=>$v) {
            $query->setParameter($k, $v);
        }

        return $query->getResult();
    }


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
                ' LEFT JOIN r.recordHasFieldStringWithLocaleValues rhfswlv WITH rhfswlv.refusedAt IS NULL AND rhfswlv.approvedAt IS NULL '.
                ' LEFT JOIN r.recordHasFieldTextValues rhftv WITH rhftv.refusedAt IS NULL AND rhftv.approvedAt IS NULL '.
                ' LEFT JOIN r.recordHasFieldLatLngValues rhfllv WITH rhfllv.refusedAt IS NULL AND rhfllv.approvedAt IS NULL '.
                ' LEFT JOIN r.recordHasFieldEmailValues rhfev WITH rhfev.refusedAt IS NULL AND rhfev.approvedAt IS NULL '.
                ' LEFT JOIN r.recordHasFieldURLValues rhfuv WITH rhfuv.refusedAt IS NULL AND rhfuv.approvedAt IS NULL '.
                ' LEFT JOIN r.recordHasFieldBooleanValues rhfbv WITH rhfbv.refusedAt IS NULL AND rhfbv.approvedAt IS NULL '.
                ' LEFT JOIN r.recordHasFieldMultiSelectValues rhfmsv1 WITH rhfmsv1.additionApprovedAt IS NULL AND rhfmsv1.additionRefusedAt IS NULL '.
                ' LEFT JOIN r.recordHasFieldMultiSelectValues rhfmsv2 WITH rhfmsv2.removalCreatedAt IS NOT NULL AND rhfmsv2.removalApprovedAt IS NULL AND rhfmsv2.removalRefusedAt IS NULL '.
                ' LEFT JOIN r.recordHasStates rhs WITH rhs.refusedAt IS NULL AND rhs.approvedAt IS NULL '.
                ' LEFT JOIN r.recordReports rr WITH rr.resolvedAt IS NULL '.
                ' WHERE r.directory = :directory AND '.
                '(rhfsv.id IS NOT NULL OR rhftv.id IS NOT NULL OR rhfbv.id IS NOT NULL OR rhfllv.id IS NOT NULL OR rhfev.id IS NOT NULL OR
                    rhfuv.id IS NOT NULL OR rhs.id IS NOT NULL OR rr.id IS NOT NULL OR rhfmsv1.id IS NOT NULL OR rhfmsv2.id IS NOT NULL OR rhfswlv.id IS NOT NULL )  '.
                'GROUP BY r.id '
            )
            ->setMaxResults(50)
            ->setParameter('directory', $directory)
            ->getResult();
    }


}
