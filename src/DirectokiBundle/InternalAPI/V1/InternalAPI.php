<?php

namespace DirectokiBundle\InternalAPI\V1;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeText;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueString;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueText;
use DirectokiBundle\InternalAPI\V1\Model\Record;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class InternalAPI {

    protected $container;

    function __construct( $container ) {
        $this->container = $container;
    }


    function getPublishedRecords($projectID, $directoryID) {

        $doctrine = $this->container->get('doctrine')->getManager();

        $project = $doctrine->getRepository('DirectokiBundle:Project')->findOneByPublicId($projectID);
        if (!$project) {
            throw new \Exception("Not Found Project");
        }

        $directory = $doctrine->getRepository('DirectokiBundle:Directory')->findOneBy(array('project'=>$project, 'publicId'=>$directoryID));
        if (!$directory) {
            throw new \Exception("Not Found Directory");
        }

        // Get data, return
        $out = array();
        $fields = $doctrine->getRepository('DirectokiBundle:Field')->findForDirectory($directory);

        foreach($doctrine->getRepository('DirectokiBundle:Record')->findBy(array('directory'=>$directory,'cachedState'=>RecordHasState::STATE_PUBLISHED)) as $record) {

            $fieldValues = array();
            foreach($fields as $field) {
                $fieldType = $this->container->get( 'directoki_field_type_service' )->getByField( $field );
                $tmp       = $fieldType->getLatestFieldValuesFromCache( $field, $record );
                
                if ( $field->getFieldType() == FieldTypeString::FIELD_TYPE_INTERNAL && $tmp[0] ) {
                    $fieldValues[ $field->getPublicId() ] = new FieldValueString( $field->getPublicId(), $field->getTitle(), $tmp[0]->getValue() );
                } else if ( $field->getFieldType() == FieldTypeText::FIELD_TYPE_INTERNAL && $tmp[0] ) {
                    $fieldValues[ $field->getPublicId() ] = new FieldValueText( $field->getPublicId(), $field->getTitle(), $tmp[0]->getValue() );
                }
            }
            $out[] = new Record($record->getPublicId(), $fieldValues);
        }

        return $out;

    }

    function getPublishedRecord($projectID, $directoryID, $recordID) {

        $doctrine = $this->container->get('doctrine')->getManager();

        $project = $doctrine->getRepository('DirectokiBundle:Project')->findOneByPublicId($projectID);
        if (!$project) {
            throw new \Exception("Not Found Project");
        }

        $directory = $doctrine->getRepository('DirectokiBundle:Directory')->findOneBy(array('project'=>$project, 'publicId'=>$directoryID));
        if (!$directory) {
            throw new \Exception("Not Found Directory");
        }

        $record = $doctrine->getRepository('DirectokiBundle:Record')->findOneBy(array('directory'=>$directory, 'publicId'=>$recordID));
        if (!$record) {
            throw new \Exception("Not Found Record");
        }

        // check published
        $recordHasState = $doctrine->getRepository('DirectokiBundle:RecordHasState')->getLatestStateForRecord($record);
        if ($recordHasState->getState() != RecordHasState::STATE_PUBLISHED) {
            throw new \Exception("Not Found State");
        }

        // Get data, return

        $fields = $doctrine->getRepository('DirectokiBundle:Field')->findForDirectory($directory);

        $fieldValues = array();
        foreach($fields as $field) {

            $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
            $tmp = $fieldType->getLatestFieldValues($field, $record);

            if ($field->getFieldType() == FieldTypeString::FIELD_TYPE_INTERNAL) {
                $fieldValues[$field->getPublicId()] = new FieldValueString($field->getPublicId(), $field->getTitle(), $tmp[0]->getValue());
            } else if ($field->getFieldType() == FieldTypeText::FIELD_TYPE_INTERNAL) {
                $fieldValues[$field->getPublicId()] = new FieldValueText($field->getPublicId(), $field->getTitle(), $tmp[0]->getValue());
            }

        }

        return new Record($record->getPublicId(), $fieldValues);

    }

}
