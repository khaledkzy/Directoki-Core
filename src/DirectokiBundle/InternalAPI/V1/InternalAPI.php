<?php

namespace DirectokiBundle\InternalAPI\V1;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeText;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueLatLng;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueLatLngEdit;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueString;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueStringEdit;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueText;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueTextEdit;
use DirectokiBundle\InternalAPI\V1\Model\Record;
use DirectokiBundle\InternalAPI\V1\Model\RecordCreate;
use DirectokiBundle\InternalAPI\V1\Model\RecordEdit;

use Symfony\Component\HttpFoundation\Request;


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
                } else if ( $field->getFieldType() == FieldTypeLatLng::FIELD_TYPE_INTERNAL && $tmp[0] ) {
                    $fieldValues[ $field->getPublicId() ] = new FieldValueLatLng( $field->getPublicId(), $field->getTitle(), $tmp[0]->getLat(), $tmp[0]->getLng()  );
                }
            }
            $out[] = new Record($project->getPublicId(), $directory->getPublicId(), $record->getPublicId(), $fieldValues);
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
            } else if ($field->getFieldType() == FieldTypeLatLng::FIELD_TYPE_INTERNAL) {
                $fieldValues[$field->getPublicId()] = new FieldValueLatLng($field->getPublicId(), $field->getTitle(), $tmp[0]->getLat(), $tmp[0]->getLng());
            }

        }

        return new Record($project->getPublicId(), $directory->getPublicId(), $record->getPublicId(), $fieldValues);

    }

    function getPublishedRecordEdit(Record $record) {
        return new RecordEdit($record);
    }


    function savePublishedRecordEdit(RecordEdit $recordEdit, Request $request = null) {

        $doctrine = $this->container->get('doctrine')->getManager();

        $project = $doctrine->getRepository('DirectokiBundle:Project')->findOneByPublicId($recordEdit->getProjectPublicId());
        if (!$project) {
            throw new \Exception("Not Found Project");
        }

        $directory = $doctrine->getRepository('DirectokiBundle:Directory')->findOneBy(array('project'=>$project, 'publicId'=>$recordEdit->getDirectoryPublicId()));
        if (!$directory) {
            throw new \Exception("Not Found Directory");
        }

        $record = $doctrine->getRepository('DirectokiBundle:Record')->findOneBy(array('directory'=>$directory, 'publicId'=>$recordEdit->getPublicID()));
        if (!$record) {
            throw new \Exception("Not Found Record");
        }


        $event = $this->container->get('directoki_event_builder_service')->build(
            $project,
            $recordEdit->getUser(),
            $request,
            $recordEdit->getComment()
        );

        $fieldDataToSave = array();
        foreach ( $recordEdit->getFieldValueEdits() as $fieldEdit ) {

            $field = $doctrine->getRepository('DirectokiBundle:Field')->findOneBy(array('directory'=>$directory, 'publicId'=>$fieldEdit->getPublicID()));

            $fieldType = $this->container->get( 'directoki_field_type_service' )->getByField( $field );

            $fieldDataToSave = array_merge($fieldDataToSave, $fieldType->processInternalAPI1Record($fieldEdit, $directory, $record, $event));

        }

        if ($fieldDataToSave) {

            $email = $recordEdit->getEmail();
            if ($email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $event->setContact( $doctrine->getRepository( 'DirectokiBundle:Contact' )->findOrCreateByEmail($project, $email));
                } else {
                    $this->get('logger')->error('An edit on project '.$project->getPublicId().' directory '.$directory->getPublicId().' record '.$record->getPublicId().' had an email address we did not recognise: ' . $email);
                }
            }
            $doctrine->persist($event);

            foreach($fieldDataToSave as $entityToSave) {
                $doctrine->persist($entityToSave);
            }

            $doctrine->flush();

            return true;

        } else {
            return false;
        }
    }


    function getRecordCreate($projectPublicId, $directoryPublicId) {
        $doctrine = $this->container->get('doctrine')->getManager();

        $project = $doctrine->getRepository('DirectokiBundle:Project')->findOneByPublicId($projectPublicId);
        if (!$project) {
            throw new \Exception("Not Found Project");
        }

        $directory = $doctrine->getRepository('DirectokiBundle:Directory')->findOneBy(array('project'=>$project, 'publicId'=>$directoryPublicId));
        if (!$directory) {
            throw new \Exception("Not Found Directory");
        }

        $fields = array();
        foreach($doctrine->getRepository('DirectokiBundle:Field')->findForDirectory($directory) as $field) {

            if ($field->getFieldType() == FieldTypeString::FIELD_TYPE_INTERNAL) {
                $fields[$field->getPublicId()] = new FieldValueStringEdit(null, $field);
            } else if ($field->getFieldType() == FieldTypeText::FIELD_TYPE_INTERNAL) {
                $fields[$field->getPublicId()] = new FieldValueTextEdit(null, $field);
            } else if ($field->getFieldType() == FieldTypeLatLng::FIELD_TYPE_INTERNAL) {
                $fields[$field->getPublicId()] = new FieldValueLatLngEdit(null, $field);
            }
        }

        return new RecordCreate($projectPublicId, $directoryPublicId, $fields);

    }

    function saveRecordCreate(RecordCreate $recordCreate, Request $request = null) {

        $doctrine = $this->container->get('doctrine')->getManager();

        $project = $doctrine->getRepository('DirectokiBundle:Project')->findOneByPublicId($recordCreate->getProjectPublicId());
        if (!$project) {
            throw new \Exception("Not Found Project");
        }

        $directory = $doctrine->getRepository('DirectokiBundle:Directory')->findOneBy(array('project'=>$project, 'publicId'=>$recordCreate->getDirectoryPublicId()));
        if (!$directory) {
            throw new \Exception("Not Found Directory");
        }


        $event = $this->container->get('directoki_event_builder_service')->build(
            $project,
            $recordCreate->getUser(),
            $request,
            $recordCreate->getComment()
        );

        $fieldDataToSave = array();
        foreach ( $recordCreate->getFieldValueEdits() as $fieldEdit ) {

            $field = $doctrine->getRepository('DirectokiBundle:Field')->findOneBy(array('directory'=>$directory, 'publicId'=>$fieldEdit->getPublicID()));

            $fieldType = $this->container->get( 'directoki_field_type_service' )->getByField( $field );

            $fieldDataToSave = array_merge($fieldDataToSave, $fieldType->processInternalAPI1Record($fieldEdit, $directory, null, $event));

        }

        if ($fieldDataToSave) {

            $email = $recordCreate->getEmail();
            if ($email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $event->setContact( $doctrine->getRepository( 'DirectokiBundle:Contact' )->findOrCreateByEmail($project, $email));
                } else {
                    $this->get('logger')->error('An edit on project '.$project->getPublicId().' directory '.$directory->getPublicId().' new record had an email address we did not recognise: ' . $email);
                }
            }
            $doctrine->persist($event);

            $record = new \DirectokiBundle\Entity\Record();
            $record->setDirectory($directory);
            $record->setCreationEvent($event);
            $record->setCachedState(RecordHasState::STATE_DRAFT);
            $doctrine->persist($record);

            // Also record a request to publish this record but don't approve it - moderator will do that.
            $recordHasState = new RecordHasState();
            $recordHasState->setRecord($record);
            $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
            $recordHasState->setCreationEvent($event);
            $doctrine->persist($recordHasState);

            foreach($fieldDataToSave as $entityToSave) {
                $entityToSave->setRecord($record);
                $doctrine->persist($entityToSave);
            }

            $doctrine->flush();

            return true;

        } else {
            return false;
        }

    }


}
