<?php

namespace DirectokiBundle\Repository;

use DirectokiBundle\Entity\Contact;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class SelectValueRepository extends EntityRepository {


    public function doesPublicIdExist($id, Field $field)
    {
        if ($field->getId()) {
            $s =  $this->getEntityManager()
                       ->createQuery(
                           ' SELECT sv FROM DirectokiBundle:SelectValue sv'.
                           ' WHERE sv.field = :field AND sv.publicId = :public_id'
                       )
                       ->setParameter('field', $field)
                       ->setParameter('public_id', $id)
                       ->getResult();
            return (boolean)$s;
        } else {
            return false;
        }
    }

    public function findByTitleFromUser(Field $field, $title) {
        $s =  $this->getEntityManager()
                   ->createQuery(
                       ' SELECT sv FROM DirectokiBundle:SelectValue sv'.
                       ' WHERE sv.field = :field AND TRIM(LOWER(sv.title)) = :title'
                   )
                   ->setParameter('field', $field)
                   ->setParameter('title', mb_strtolower(trim($title)))
                   ->getResult();
        return count($s) ? $s[0] : null;

    }



}

