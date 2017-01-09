<?php

namespace DirectokiBundle\FieldType;


use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldLatLngValue;
use DirectokiBundle\Entity\RecordHasLatLngFieldValue;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\User;
use DirectokiBundle\Form\Type\RecordHasFieldLatLngValueType;
use DirectokiBundle\Form\Type\RecordHasLatLngFieldValueType;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 *
 */
class FieldTypeLatLng extends  BaseFieldType {

    const FIELD_TYPE_INTERNAL = 'latlng';
    const FIELD_TYPE_API1 = 'latlng';

    public function getLatestFieldValue(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldLatLngValue');

        $r = $repo->findLatestFieldValue($field, $record);

        if (!$r) {
            $r = new RecordHasFieldLatLngValue();
        }

        return $r;

    }

    public function getFieldValuesToModerate(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldLatLngValue');

        return $repo->getFieldValuesToModerate($field, $record);
    }

    public function getLabel() {
        return "LatLng";
    }

    public function getEditFieldForm( Field $field, Record $record ) {

        $dataHasField = $this->getLatestFieldValue($field, $record);

        return new RecordHasFieldLatLngValueType($dataHasField);
    }

    public function getEditFieldFormNewRecords( Field $field, Record $record, Event $event, $form, User $user = null, $approve = false ) {

        $newRecordHasFieldValues = new RecordHasFieldLatLngValue();
        $newRecordHasFieldValues->setRecord($record);
        $newRecordHasFieldValues->setField($field);
        $newRecordHasFieldValues->setLat($form->get('lat')->getData());
        $newRecordHasFieldValues->setLng($form->get('lng')->getData());
        $newRecordHasFieldValues->setCreationEvent($event);
        $newRecordHasFieldValues->setCreatedBy($user);
        if ($approve) {
            $newRecordHasFieldValues->setApprovedAt(new \DateTime());
            $newRecordHasFieldValues->setApprovalEvent($event);
        }

        return array ($newRecordHasFieldValues);
    }

    public function getViewTemplate() {
        return '@Directoki/FieldType/LatLng/view.html.twig';
    }

    public function getAPIJSON( Field $field, Record $record ) {
        $latest = $this->getLatestFieldValue($field, $record);
        return array('lat'=>$latest->getLat(), 'lng'=>$latest->getLng());
    }



    public function processAPI1Record(Field $field, Record $record = null, ParameterBag $parameterBag) {
        // TODO
        return array();
    }

    public function getEmails( Field $field, Record $record ) {
        // TODO: Implement getEmails() method.
        return array();
    }

}
