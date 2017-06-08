<?php

namespace DirectokiBundle\FieldType;


use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldMultiSelectValue;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\SelectValue;
use Symfony\Component\Form\Form;
use DirectokiBundle\ImportCSVLineResult;
use DirectokiBundle\InternalAPI\V1\Model\BaseFieldValue;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\Form\Type\RecordHasFieldMultiSelectValueType;
use DirectokiBundle\ModerationNeeded\ModerationNeededRecordHasFieldMultiValueAddition;
use DirectokiBundle\ModerationNeeded\ModerationNeededRecordHasFieldMultiValueRemoval;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 *
 */
class FieldTypeMultiSelect extends  BaseFieldType
{

    const FIELD_TYPE_INTERNAL = 'multiselect';
    const FIELD_TYPE_API1 = 'multiselect';

    public function getLatestFieldValues(Field $field, Record $record)
    {

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue');

        $r = $repo->findLatestFieldValues($field, $record);

        return $r;

    }


    public function getLatestFieldValuesFromCache( Field $field, Record $record ) {

        if ($record->getCachedFields() && isset($record->getCachedFields()[$field->getId()])  && is_array($record->getCachedFields()[$field->getId()]['value'])) {

            $out = array();

            foreach($record->getCachedFields()[$field->getId()]['value'] as $data) {
                $selectValue = new SelectValue();
                $selectValue->setTitle($data['title']);
                $selectValue->setPublicId($data['publicId']);

                $recordHasFieldMultiSelectValue = new RecordHasFieldMultiSelectValue();
                $recordHasFieldMultiSelectValue->setSelectValue($selectValue);

                $out[] = $recordHasFieldMultiSelectValue;
            }

            return $out;
        }
        return array();

    }

    public function getFieldValuesToModerate(Field $field, Record $record)
    {
        return array();
    }


    public function getModerationsNeeded(Field $field, Record $record)
    {

        $out = array();

        $repo = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue');

        foreach ($repo->getAdditionFieldValuesToModerate($field, $record) as $fieldValue) {
            $out[] = new ModerationNeededRecordHasFieldMultiValueAddition($fieldValue);
        }
        foreach ($repo->getRemovalFieldValuesToModerate($field, $record) as $fieldValue) {
            $out[] = new ModerationNeededRecordHasFieldMultiValueRemoval($fieldValue);
        }
        return $out;
    }

    public function getLabel()
    {
        return "Multi Select";
    }

    public function isMultipleType()
    {
        return true;
    }

    public function getEditFieldForm(Field $field, Record $record)
    {

        return new RecordHasFieldMultiSelectValueType($this->container, $field, $record);
    }

    public function getEditFieldFormNewRecords(Field $field, Record $record, Event $event, $form, User $user = null, $approve = false)
    {

        $repoSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');
        $repoRecordHasFieldMultiSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue');

        $out = array();
        foreach ($repoSelectValue->findBy(array('field' => $field)) as $selectValue) {

            if ($form->get('value_' . $selectValue->getPublicId())->getData()) {

                // User has selected this value! Check it's not there already, and add it!

                if (!$repoRecordHasFieldMultiSelectValue->doesRecordHaveFieldHaveValue($record, $field, $selectValue)) {

                    $newRecordHasMultiSelectValues = new RecordHasFieldMultiSelectValue();
                    $newRecordHasMultiSelectValues->setRecord($record);
                    $newRecordHasMultiSelectValues->setField($field);
                    $newRecordHasMultiSelectValues->setSelectValue($selectValue);
                    $newRecordHasMultiSelectValues->setAdditionCreationEvent($event);
                    if ($approve) {
                        $newRecordHasMultiSelectValues->setAdditionApprovedAt(new \DateTime());
                        $newRecordHasMultiSelectValues->setAdditionApprovalEvent($event);
                    }
                    $out[] = $newRecordHasMultiSelectValues;

                }

            } else {

                $recordHasMultiSelectValue = $repoRecordHasFieldMultiSelectValue->getRecordFieldHasValue($record, $field, $selectValue);

                if ($recordHasMultiSelectValue) {

                    $recordHasMultiSelectValue->setRemovalCreationEvent($event);
                    $recordHasMultiSelectValue->setRemovalCreatedAt(new \DateTime());
                    if ($approve) {
                        $recordHasMultiSelectValue->setRemovalApprovedAt(new \DateTime());
                        $recordHasMultiSelectValue->setRemovalApprovalEvent($event);
                    }
                    $out[] = $recordHasMultiSelectValue;

                }

            }

        }

        return $out;
    }

    public function getViewTemplate()
    {
        return '@Directoki/FieldType/MultiSelect/view.html.twig';
    }

    public function getAPIJSON(Field $field, Record $record, $useCachedData = false)
    {
        // TODO respect $useCachedData! (Must actually implement  getLatestFieldValuesFromCache first!)
        $out = array();
        foreach ($this->getLatestFieldValues($field, $record) as $value) {
            $out[] = array(
                'value' => array(
                    'id' => $value->getSelectValue()->getPublicId(),
                    'title' => $value->getSelectValue()->getTitle(),
                )
            );
        }
        return array('values' => $out);
    }

    public function processAPI1Record(Field $field, Record $record = null, ParameterBag $parameterBag, Event $event)
    {
        $out = array();
        if ($parameterBag->has('field_' . $field->getPublicId() . '_add_title')) {
            $newValue = $parameterBag->get('field_' . $field->getPublicId() . '_add_title');
            if (is_array($newValue)) {
                foreach ($newValue as $nv) {
                    $out = array_merge($out, $this->processAPI1RecordAddStringValue($nv, $field, $record, $parameterBag, $event));
                }
            } else {
                $out = array_merge($out, $this->processAPI1RecordAddStringValue($newValue, $field, $record, $parameterBag, $event));
            }
        }
        if ($parameterBag->has('field_' . $field->getPublicId() . '_remove_id')) {
            $removeIdValue = $parameterBag->get('field_' . $field->getPublicId() . '_remove_id');
            if (is_array($removeIdValue)) {
                foreach ($removeIdValue as $riv) {
                    $out = array_merge($out, $this->processAPI1RecordRemoveStringId($riv, $field, $record, $parameterBag, $event));
                }
            } else {
                $out = array_merge($out, $this->processAPI1RecordRemoveStringId($removeIdValue, $field, $record, $parameterBag, $event));
            }
        }
        return $out;
    }

    public function processInternalAPI1Record(BaseFieldValue $fieldValueEdit, Directory $directory, Record $record = null, Event $event) {
        // TODO
        return array();
    }

    protected function processAPI1RecordAddStringValue($value, Field $field, Record $record = null, ParameterBag $parameterBag, Event $event)
    {

        $repoSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');

        $valueObject = $repoSelectValue->findByTitleFromUser($field, $value);

        if (!$valueObject) {
            return array(); // TODO We can't find the value the user passed.
        }

        $repoRecordHasFieldMultiSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue');

        if ($repoRecordHasFieldMultiSelectValue->doesRecordHaveFieldHaveValue($record, $field, $valueObject)) {
            return array(); // TODO Value is already set!
        }

        // TODO check someone else has not already tried to add value!

        $newRecordHasFieldValues = new RecordHasFieldMultiSelectValue();
        $newRecordHasFieldValues->setRecord($record);
        $newRecordHasFieldValues->setField($field);
        $newRecordHasFieldValues->setSelectValue($valueObject);
        $newRecordHasFieldValues->setAdditionCreationEvent($event);
        return array($newRecordHasFieldValues);

    }

    protected function processAPI1RecordRemoveStringId($value, Field $field, Record $record = null, ParameterBag $parameterBag, Event $event)
    {
        $repoSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');


        $valueObject = $repoSelectValue->findOneBy(array('field'=>$field, 'publicId'=>$value));

        if (!$valueObject) {
            return array(); // TODO We can't find the value the user passed.
        }


        $repoRecordHasFieldMultiSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue');

        $recordFieldHasValue = $repoRecordHasFieldMultiSelectValue->getRecordFieldHasValue($record, $field, $valueObject);

        if (!$recordFieldHasValue) {
            return array(); // TODO Value is not currently set!
        }

        if ($recordFieldHasValue->getRemovalCreationEvent()) {
            return array(); // TODO Someone else has already tried to remove value!
        }

        $recordFieldHasValue->setRemovalCreationEvent($event);
        $recordFieldHasValue->setRemovalCreatedAt(new \DateTime());
        return array($recordFieldHasValue);

    }

    public function parseCSVLineData( Field $field, $fieldConfig, $lineData,  Record $record, Event $creationEvent, $published=false ) {

        $entitesToSave = array();
        $repoSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');

        if (isset($fieldConfig['add_value_id'])) {
            foreach(explode(",", $fieldConfig['add_value_id']) as $valuePublicId) {
                if (trim($valuePublicId)) {
                    $valueObject = $repoSelectValue->findOneBy(array('field' => $field, 'publicId' => trim($valuePublicId)));
                    if ($valueObject) {
                        $newRecordHasFieldValues = new RecordHasFieldMultiSelectValue();
                        $newRecordHasFieldValues->setRecord($record);
                        $newRecordHasFieldValues->setField($field);
                        $newRecordHasFieldValues->setSelectValue($valueObject);
                        $newRecordHasFieldValues->setAdditionCreationEvent($creationEvent);
                        if ($published) {
                            $newRecordHasFieldValues->setAdditionApprovalEvent($creationEvent);
                        }
                        $entitesToSave[] = $newRecordHasFieldValues;
                    }
                }
            }
        }

        if (isset($fieldConfig['add_title_column'])) {
            foreach (explode(",", $lineData[$fieldConfig['add_title_column']]) as $valueTitle) {
                $valueTitle = trim($valueTitle);
                if ($valueTitle) {
                    $valueObject = $repoSelectValue->findByTitleFromUser($field, $valueTitle);
                    if (!$valueObject) {
                        $valueObject = new SelectValue();
                        $valueObject->setCreationEvent($creationEvent);
                        $valueObject->setTitle(trim($valueTitle));
                        $valueObject->setField($field);
                        $entitesToSave[] = $valueObject;
                    }
                    $newRecordHasFieldValues = new RecordHasFieldMultiSelectValue();
                    $newRecordHasFieldValues->setRecord($record);
                    $newRecordHasFieldValues->setField($field);
                    $newRecordHasFieldValues->setSelectValue($valueObject);
                    $newRecordHasFieldValues->setAdditionCreationEvent($creationEvent);
                    if ($published) {
                        $newRecordHasFieldValues->setAdditionApprovalEvent($creationEvent);
                    }
                    $entitesToSave[] = $newRecordHasFieldValues;
                }
            }
        }

        if ($entitesToSave) {
            $debugOutput = array();
            foreach($entitesToSave as $record) {
                if ($record instanceof RecordHasFieldMultiSelectValue) {
                    $debugOutput[] = $record->getSelectValue()->getTitle();
                } else if ($record instanceof SelectValue) {
                    $debugOutput[] = "New Select Value: ". $record->getTitle();
                }
            }
            return new ImportCSVLineResult(
                implode(', ', $debugOutput),
                $entitesToSave
            );
        }

    }

    public function getDataForCache( Field $field, Record $record ) {
        $out = array('value'=>array());
        foreach($this->getLatestFieldValues($field, $record) as $recordHasFieldMultiSelectValue) {
            $out['value'][] = array(
                'publicId'=>$recordHasFieldMultiSelectValue->getSelectValue()->getPublicId(),
                'title'=>$recordHasFieldMultiSelectValue->getSelectValue()->getTitle(),
            );
        }
        return $out;
    }

    public function addToNewRecordForm(Field $field, FormBuilderInterface $formBuilderInterface)
    {
        $repoSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');
        foreach ($repoSelectValue->findBy(array('field' => $field),array('title'=>'asc')) as $selectValue) {
            $formBuilderInterface->add($field->getPublicId().'_value_'. $selectValue->getPublicId(), CheckboxType::class, array(
                'required' => false,
                'label'=>$field->getTitle() . ': '. $selectValue->getTitle(),
            ));
        }
    }

    public function processNewRecordForm(Field $field, Record $record, Form $form, Event $creationEvent, $published = false)
    {
        $entitesToSave = array();
        $repoSelectValue = $this->container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');
        foreach ($repoSelectValue->findBy(array('field' => $field),array('title'=>'asc')) as $selectValue) {
            $value = $form->get($field->getPublicId().'_value_'. $selectValue->getPublicId())->getData();
            if ($value) {
                $newRecordHasFieldValues = new RecordHasFieldMultiSelectValue();
                $newRecordHasFieldValues->setRecord($record);
                $newRecordHasFieldValues->setField($field);
                $newRecordHasFieldValues->setSelectValue($selectValue);
                $newRecordHasFieldValues->setAdditionCreationEvent($creationEvent);
                if ($published) {
                    $newRecordHasFieldValues->setAdditionApprovalEvent($creationEvent);
                }
                $entitesToSave[] = $newRecordHasFieldValues;

            }
        }
        return $entitesToSave;
    }


}

