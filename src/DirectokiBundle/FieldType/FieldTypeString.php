<?php

namespace DirectokiBundle\FieldType;


use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasStringFieldValue;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\User;
use DirectokiBundle\Form\Type\RecordHasFieldStringValueType;
use DirectokiBundle\Form\Type\RecordHasStringFieldValueType;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 *
 */
class FieldTypeString extends  BaseFieldType {

    const FIELD_TYPE_INTERNAL = 'string';
    const FIELD_TYPE_API1 = 'string';

    public function getLatestFieldValue(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldStringValue');

        $r = $repo->findLatestFieldValue($field, $record);

        if (!$r) {
            $r = new RecordHasFieldStringValue();
        }

        return $r;

    }

    public function getFieldValuesToModerate(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldStringValue');

        return $repo->getFieldValuesToModerate($field, $record);
    }

    public function getLabel() {
        return "String";
    }

    public function getEditFieldForm( Field $field, Record $record ) {

        $dataHasField = $this->getLatestFieldValue($field, $record);

        return new RecordHasFieldStringValueType($dataHasField);
    }

    public function getEditFieldFormNewRecords( Field $field, Record $record, Event $event, $form, User $user = null, $approve = false ) {

        $newRecordHasFieldValues = new RecordHasFieldStringValue();
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
        return '@Directoki/FieldType/String/view.html.twig';
    }

    public function getAPIJSON( Field $field, Record $record ) {
        $latest = $this->getLatestFieldValue($field, $record);
        return array('value'=>$latest->getValue());
    }

    public function processAPI1Record(Field $field, Record $record = null, ParameterBag $parameterBag) {
        if ($parameterBag->has('field_'.$field->getPublicId().'_value')) {
            $currentValue = '';
            if ( $record !== null ) {
                $latestValueObject = $this->getLatestFieldValue($field, $record);
                $currentValue = $latestValueObject->getValue();
            }
            $newValue = $parameterBag->get('field_'.$field->getPublicId().'_value');
            if ($newValue != $currentValue) {
                $newRecordHasFieldValues = new RecordHasFieldStringValue();
                $newRecordHasFieldValues->setRecord($record);
                $newRecordHasFieldValues->setField($field);
                $newRecordHasFieldValues->setValue($newValue);
                return array($newRecordHasFieldValues);
            }
        }
        return array();
    }

    public function getEmails( Field $field, Record $record ) {
        // TODO: Implement getEmails() method.
        return array();
    }

}
