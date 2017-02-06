<?php

namespace DirectokiBundle\FieldType;


use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldURLValue;
use DirectokiBundle\Entity\RecordHasURLFieldValue;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\User;
use DirectokiBundle\Form\Type\RecordHasFieldURLValueType;
use DirectokiBundle\Form\Type\RecordHasURLFieldValueType;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 *
 */
class FieldTypeURL extends  BaseFieldType {

    const FIELD_TYPE_INTERNAL = 'url';
    const FIELD_TYPE_API1 = 'url';

    public function getLatestFieldValue(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldURLValue');

        $r = $repo->findLatestFieldValue($field, $record);

        if (!$r) {
            $r = new RecordHasFieldURLValue();
        }

        return $r;

    }

    public function getFieldValuesToModerate(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldURLValue');

        return $repo->getFieldValuesToModerate($field, $record);
    }

    public function getLabel() {
        return "URL";
    }

    public function getEditFieldForm( Field $field, Record $record ) {

        $dataHasField = $this->getLatestFieldValue($field, $record);

        return new RecordHasFieldURLValueType($dataHasField);
    }

    public function getEditFieldFormNewRecords( Field $field, Record $record, Event $event, $form, User $user = null, $approve = false ) {

        $newRecordHasFieldValues = new RecordHasFieldURLValue();
        $newRecordHasFieldValues->setRecord($record);
        $newRecordHasFieldValues->setField($field);
        $newRecordHasFieldValues->setValue($form->get('value')->getData());
        $newRecordHasFieldValues->setCreationEvent($event);
        $newRecordHasFieldValues->setCreatedBy($user);
        if ($approve) {
            $newRecordHasFieldValues->setApprovedAt(new \DateTime());
            $newRecordHasFieldValues->setApprovalEvent($event);
        }

        return array ($newRecordHasFieldValues);
    }

    public function getViewTemplate() {
        return '@Directoki/FieldType/URL/view.html.twig';
    }

    public function getAPIJSON( Field $field, Record $record ) {
        $latest = $this->getLatestFieldValue($field, $record);
        return array('value'=>$latest->getValue());
    }



    public function processAPI1Record(Field $field, Record $record = null, ParameterBag $parameterBag) {
        // TODO
        return array();
    }

}
