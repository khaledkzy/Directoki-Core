<?php

namespace DirectokiBundle\FieldType;


use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldEmailValue;
use DirectokiBundle\Entity\RecordHasEmailFieldValue;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\User;
use DirectokiBundle\Form\Type\RecordHasFieldEmailValueType;
use DirectokiBundle\Form\Type\RecordHasEmailFieldValueType;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 *
 */
class FieldTypeEmail extends  BaseFieldType {

    const FIELD_TYPE_INTERNAL = 'email';
    const FIELD_TYPE_API1 = 'email';

    public function getLatestFieldValue(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldEmailValue');

        $r = $repo->findLatestFieldValue($field, $record);

        if (!$r) {
            $r = new RecordHasFieldEmailValue();
        }

        return $r;

    }

    public function getFieldValuesToModerate(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldEmailValue');

        return $repo->getFieldValuesToModerate($field, $record);
    }

    public function getLabel() {
        return "Email";
    }

    public function getEditFieldForm( Field $field, Record $record ) {

        $dataHasField = $this->getLatestFieldValue($field, $record);

        return new RecordHasFieldEmailValueType($dataHasField);
    }

    public function getEditFieldFormNewRecords( Field $field, Record $record, Event $event, $form, User $user = null, $approve = false ) {

        $newRecordHasFieldValues = new RecordHasFieldEmailValue();
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
        return '@Directoki/FieldType/Email/view.html.twig';
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
