<?php

namespace DirectokiBundle\FieldType;


use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldLatLngValue;
use DirectokiBundle\Entity\Field;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\Form\Type\RecordHasFieldLatLngValueType;
use DirectokiBundle\ImportCSVLineResult;
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

    public function getLatestFieldValues(Field $field, Record $record) {
        return array($this->getLatestFieldValue($field, $record));
    }
    protected function getLatestFieldValue(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldLatLngValue');

        $r = $repo->findLatestFieldValue($field, $record);

        if (!$r) {
            $r = new RecordHasFieldLatLngValue();
        }

        return $r;

    }


    public function getLatestFieldValuesFromCache( Field $field, Record $record ) {
        // TODO: Implement getLatestFieldValuesFromCache() method.
    }

    public function getFieldValuesToModerate(Field $field, Record $record) {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldLatLngValue');

        return $repo->getFieldValuesToModerate($field, $record);
    }

    public function getModerationsNeeded(Field $field, Record $record) {
        return array();
    }

    public function getLabel() {
        return "LatLng";
    }

    public function isMultipleType() {
        return false;
    }

    public function getEditFieldForm( Field $field, Record $record ) {

        $dataHasField = $this->getLatestFieldValue($field, $record);

        return new RecordHasFieldLatLngValueType($dataHasField);
    }

    public function getEditFieldFormNewRecords( Field $field, Record $record, Event $event, $form, User $user = null, $approve = false ) {

        // TODO see if value has changed before saving!! Can return array() if not.


        $newRecordHasFieldValues = new RecordHasFieldLatLngValue();
        $newRecordHasFieldValues->setRecord($record);
        $newRecordHasFieldValues->setField($field);
        $newRecordHasFieldValues->setLat($form->get('lat')->getData());
        $newRecordHasFieldValues->setLng($form->get('lng')->getData());
        $newRecordHasFieldValues->setCreationEvent($event);
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

    public function processAPI1Record(Field $field, Record $record = null, ParameterBag $parameterBag, Event $event) {
        if ($parameterBag->has('field_'.$field->getPublicId().'_lat') && $parameterBag->has('field_'.$field->getPublicId().'_lng')) {
            $currentValueLat = null;
            $currentValueLng = null;
            if ( $record !== null ) {
                $latestValueObject = $this->getLatestFieldValue($field, $record);
                $currentValueLat = $latestValueObject->getLat();
                $currentValueLng = $latestValueObject->getLng();
            }
            $newValueLat = floatval($parameterBag->get('field_'.$field->getPublicId().'_lat'));
            $newValueLng = floatval($parameterBag->get('field_'.$field->getPublicId().'_lng'));
            if ($newValueLat != $currentValueLat || $newValueLng != $currentValueLng) {
                $newRecordHasFieldValues = new RecordHasFieldLatLngValue();
                $newRecordHasFieldValues->setRecord($record);
                $newRecordHasFieldValues->setField($field);
                $newRecordHasFieldValues->setLat($newValueLat);
                $newRecordHasFieldValues->setLng($newValueLng);
                $newRecordHasFieldValues->setCreationEvent($event);
                return array($newRecordHasFieldValues);
            }
        }
        return array();
    }

    public function parseCSVLineData( Field $field, $fieldConfig, $lineData,  Record $record, Event $creationEvent, $published=false ) {


        if (isset($fieldConfig['column_lat']) && isset($fieldConfig['column_lng'])) {

            $columnLat = intval($fieldConfig['column_lat']);
            $columnLng = intval($fieldConfig['column_lng']);
            $dataLat  = $lineData[$columnLat];
            $dataLng = $lineData[$columnLng];

            $newRecordHasFieldValues = new RecordHasFieldLatLngValue();
            $newRecordHasFieldValues->setRecord($record);
            $newRecordHasFieldValues->setField($field);
            $newRecordHasFieldValues->setLat($dataLat);
            $newRecordHasFieldValues->setLng($dataLng);
            $newRecordHasFieldValues->setCreationEvent($creationEvent);
            if ($published) {
                $newRecordHasFieldValues->setApprovalEvent($creationEvent);
            }

            return new ImportCSVLineResult(
                $dataLat. ", ". $dataLng,
                array($newRecordHasFieldValues)
            );
        }

    }

    public function getDataForCache( Field $field, Record $record ) {
        $val = $this->getLatestFieldValue($field, $record);
        return $val ? array('lat'=>$val->getLat(), 'lng'=>$val->getLng()) : array();
    }
}
