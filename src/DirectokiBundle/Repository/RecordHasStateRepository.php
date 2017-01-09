<?php

namespace DirectokiBundle\Repository;

use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasState;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordHasStateRepository extends EntityRepository {

    public function getLatestStateForRecord(Record $record) {

        $s =  $this->getEntityManager()
                   ->createQuery(
                       ' SELECT rhs FROM DirectokiBundle:RecordHasState rhs '.
                       ' WHERE  rhs.record = :record AND rhs.approvedAt IS NOT NULL ' .
                       ' ORDER BY rhs.approvedAt DESC '
                   )
                   ->setMaxResults(1)
                   ->setParameter('record', $record)
                   ->getResult();
        if (count($s)  > 0) {
            return $s[0];
        } else {
            $rhs = new RecordHasState();
            $rhs->setState(RecordHasState::STATE_DRAFT);
            return $rhs;
        }

    }

    public function findUnmoderatedForRecord(Record $record) {

        return $this->getEntityManager()
           ->createQuery(
               ' SELECT rhs FROM DirectokiBundle:RecordHasState rhs '.
               ' WHERE  rhs.record = :record AND rhs.approvedAt IS  NULL AND rhs.refusedAt IS  NULL ' .
               ' ORDER BY rhs.approvedAt DESC '
           )
           ->setParameter('record', $record)
           ->getResult();

    }

}
