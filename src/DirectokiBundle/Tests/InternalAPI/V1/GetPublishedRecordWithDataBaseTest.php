<?php


namespace DirectokiBundle\Tests\InternalAPI\V1;


use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldEmailValue;
use DirectokiBundle\Entity\RecordHasFieldLatLngValue;
use DirectokiBundle\Entity\RecordHasFieldMultiSelectValue;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasFieldTextValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\FieldType\FieldTypeEmail;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeText;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\InternalAPI\V1\InternalAPI;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class GetPublishedRecordWithDataBaseTest extends BaseTestWithDataBase
{

    public function testStringField() {

        $user = new User();
        $user->setEmail('test1@example.com');
        $user->setPassword('password');
        $user->setUsername('test1');
        $this->em->persist($user);

        $project = new Project();
        $project->setTitle('test1');
        $project->setPublicId('test1');
        $project->setOwner($user);
        $this->em->persist($project);

        $event = new Event();
        $event->setProject($project);
        $event->setUser($user);
        $this->em->persist($event);

        $directory = new Directory();
        $directory->setPublicId('resource');
        $directory->setTitleSingular('Resource');
        $directory->setTitlePlural('Resources');
        $directory->setProject($project);
        $directory->setCreationEvent($event);
        $this->em->persist($directory);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $record->setCachedState(RecordHasState::STATE_PUBLISHED);
        $record->setPublicId('r1');
        $this->em->persist($record);

        $recordHasState = new RecordHasState();
        $recordHasState->setRecord($record);
        $recordHasState->setCreationEvent($event);
        $recordHasState->setApprovalEvent($event);
        $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
        $this->em->persist($recordHasState);

        $field = new Field();
        $field->setTitle('Title');
        $field->setPublicId('title');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeString::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);


        $recordHasFieldStringValue = new RecordHasFieldStringValue();
        $recordHasFieldStringValue->setRecord($record);
        $recordHasFieldStringValue->setField($field);
        $recordHasFieldStringValue->setValue('My Title Rocks');
        $recordHasFieldStringValue->setApprovedAt(new \DateTime());
        $recordHasFieldStringValue->setCreationEvent($event);
        $this->em->persist($recordHasFieldStringValue);

        $this->em->flush();

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $record = $internalAPI->getPublishedRecord("test1","resource","r1");

        $this->assertEquals('r1', $record->getPublicId());
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueString', get_class($record->getFieldValue('title')));
        $this->assertEquals('Title', $record->getFieldValue('title')->getTitle());
        $this->assertEquals('My Title Rocks', $record->getFieldValue('title')->getValue());

    }

    public function testTextField() {

        $user = new User();
        $user->setEmail('test1@example.com');
        $user->setPassword('password');
        $user->setUsername('test1');
        $this->em->persist($user);

        $project = new Project();
        $project->setTitle('test1');
        $project->setPublicId('test1');
        $project->setOwner($user);
        $this->em->persist($project);

        $event = new Event();
        $event->setProject($project);
        $event->setUser($user);
        $this->em->persist($event);

        $directory = new Directory();
        $directory->setPublicId('resource');
        $directory->setTitleSingular('Resource');
        $directory->setTitlePlural('Resources');
        $directory->setProject($project);
        $directory->setCreationEvent($event);
        $this->em->persist($directory);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $record->setCachedState(RecordHasState::STATE_PUBLISHED);
        $record->setPublicId('r1');
        $this->em->persist($record);

        $recordHasState = new RecordHasState();
        $recordHasState->setRecord($record);
        $recordHasState->setCreationEvent($event);
        $recordHasState->setApprovalEvent($event);
        $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
        $this->em->persist($recordHasState);

        $field = new Field();
        $field->setTitle('Description');
        $field->setPublicId('description');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeText::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);


        $recordHasFieldTextValue = new RecordHasFieldTextValue();
        $recordHasFieldTextValue->setRecord($record);
        $recordHasFieldTextValue->setField($field);
        $recordHasFieldTextValue->setValue('Test Description is very nice');
        $recordHasFieldTextValue->setApprovedAt(new \DateTime());
        $recordHasFieldTextValue->setCreationEvent($event);
        $this->em->persist($recordHasFieldTextValue);

        $this->em->flush();

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $record = $internalAPI->getPublishedRecord("test1","resource","r1");

        $this->assertEquals('r1', $record->getPublicId());
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueText', get_class($record->getFieldValue('description')));
        $this->assertEquals('Description', $record->getFieldValue('description')->getTitle());
        $this->assertEquals('Test Description is very nice', $record->getFieldValue('description')->getValue());

    }





    public function testLatLngField() {

        $user = new User();
        $user->setEmail('test1@example.com');
        $user->setPassword('password');
        $user->setUsername('test1');
        $this->em->persist($user);

        $project = new Project();
        $project->setTitle('test1');
        $project->setPublicId('test1');
        $project->setOwner($user);
        $this->em->persist($project);

        $event = new Event();
        $event->setProject($project);
        $event->setUser($user);
        $this->em->persist($event);

        $directory = new Directory();
        $directory->setPublicId('resource');
        $directory->setTitleSingular('Resource');
        $directory->setTitlePlural('Resources');
        $directory->setProject($project);
        $directory->setCreationEvent($event);
        $this->em->persist($directory);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $record->setCachedState(RecordHasState::STATE_PUBLISHED);
        $record->setPublicId('r1');
        $this->em->persist($record);

        $recordHasState = new RecordHasState();
        $recordHasState->setRecord($record);
        $recordHasState->setCreationEvent($event);
        $recordHasState->setApprovalEvent($event);
        $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
        $this->em->persist($recordHasState);

        $field = new Field();
        $field->setTitle('Map');
        $field->setPublicId('map');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeLatLng::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);


        $recordHasFieldLatLngValue = new RecordHasFieldLatLngValue();
        $recordHasFieldLatLngValue->setRecord($record);
        $recordHasFieldLatLngValue->setField($field);
        $recordHasFieldLatLngValue->setLat(45.78);
        $recordHasFieldLatLngValue->setLng(-3.89);
        $recordHasFieldLatLngValue->setApprovedAt(new \DateTime());
        $recordHasFieldLatLngValue->setCreationEvent($event);
        $this->em->persist($recordHasFieldLatLngValue);

        $this->em->flush();

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $record = $internalAPI->getPublishedRecord("test1","resource","r1");

        $this->assertEquals('r1', $record->getPublicId());
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueLatLng', get_class($record->getFieldValue('map')));
        $this->assertEquals('Map', $record->getFieldValue('map')->getTitle());
        $this->assertEquals(45.78, $record->getFieldValue('map')->getLat());
        $this->assertEquals(-3.89, $record->getFieldValue('map')->getLng());

    }


    public function testMultiSelectField() {

        $user = new User();
        $user->setEmail('test1@example.com');
        $user->setPassword('password');
        $user->setUsername('test1');
        $this->em->persist($user);

        $project = new Project();
        $project->setTitle('test1');
        $project->setPublicId('test1');
        $project->setOwner($user);
        $this->em->persist($project);

        $event = new Event();
        $event->setProject($project);
        $event->setUser($user);
        $this->em->persist($event);

        $directory = new Directory();
        $directory->setPublicId('resource');
        $directory->setTitleSingular('Resource');
        $directory->setTitlePlural('Resources');
        $directory->setProject($project);
        $directory->setCreationEvent($event);
        $this->em->persist($directory);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $record->setCachedState(RecordHasState::STATE_PUBLISHED);
        $record->setPublicId('r1');
        $this->em->persist($record);

        $recordHasState = new RecordHasState();
        $recordHasState->setRecord($record);
        $recordHasState->setCreationEvent($event);
        $recordHasState->setApprovalEvent($event);
        $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
        $this->em->persist($recordHasState);

        $field = new Field();
        $field->setTitle('Topics');
        $field->setPublicId('topics');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeMultiSelect::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $selectValue = new SelectValue();
        $selectValue->setField($field);
        $selectValue->setTitle('Cats');
        $selectValue->setPublicId('cats');
        $selectValue->setCreationEvent($event);
        $this->em->persist($selectValue);

        $recordHasFieldMultiSelectValue = new RecordHasFieldMultiSelectValue();
        $recordHasFieldMultiSelectValue->setRecord($record);
        $recordHasFieldMultiSelectValue->setField($field);
        $recordHasFieldMultiSelectValue->setSelectValue($selectValue);
        $recordHasFieldMultiSelectValue->setAdditionCreationEvent($event);
        $recordHasFieldMultiSelectValue->setAdditionApprovalEvent($event);
        $this->em->persist($recordHasFieldMultiSelectValue);

        $this->em->flush();

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $record = $internalAPI->getPublishedRecord("test1","resource","r1");

        $this->assertEquals('r1', $record->getPublicId());
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueMultiSelect', get_class($record->getFieldValue('topics')));
        $this->assertEquals('Topics', $record->getFieldValue('topics')->getTitle());

        $values = $record->getFieldValue('topics')->getSelectValues();
        $this->assertEquals(1, count($values));

        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\SelectValue', get_class($values[0]));
        $this->assertEquals('Cats', $values[0]->getTitle());
        $this->assertEquals('cats', $values[0]->getId());
    }



    public function testEmailField() {

        $user = new User();
        $user->setEmail('test1@example.com');
        $user->setPassword('password');
        $user->setUsername('test1');
        $this->em->persist($user);

        $project = new Project();
        $project->setTitle('test1');
        $project->setPublicId('test1');
        $project->setOwner($user);
        $this->em->persist($project);

        $event = new Event();
        $event->setProject($project);
        $event->setUser($user);
        $this->em->persist($event);

        $directory = new Directory();
        $directory->setPublicId('resource');
        $directory->setTitleSingular('Resource');
        $directory->setTitlePlural('Resources');
        $directory->setProject($project);
        $directory->setCreationEvent($event);
        $this->em->persist($directory);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $record->setCachedState(RecordHasState::STATE_PUBLISHED);
        $record->setPublicId('r1');
        $this->em->persist($record);

        $recordHasState = new RecordHasState();
        $recordHasState->setRecord($record);
        $recordHasState->setCreationEvent($event);
        $recordHasState->setApprovalEvent($event);
        $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
        $this->em->persist($recordHasState);

        $field = new Field();
        $field->setTitle('Email');
        $field->setPublicId('email');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeEmail::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);


        $recordHasFieldEmailValue = new RecordHasFieldEmailValue();
        $recordHasFieldEmailValue->setRecord($record);
        $recordHasFieldEmailValue->setField($field);
        $recordHasFieldEmailValue->setValue('test@example.com');
        $recordHasFieldEmailValue->setApprovedAt(new \DateTime());
        $recordHasFieldEmailValue->setCreationEvent($event);
        $this->em->persist($recordHasFieldEmailValue);

        $this->em->flush();

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $record = $internalAPI->getPublishedRecord("test1","resource","r1");

        $this->assertEquals('r1', $record->getPublicId());
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueEmail', get_class($record->getFieldValue('email')));
        $this->assertEquals('Email', $record->getFieldValue('email')->getTitle());
        $this->assertEquals('test@example.com', $record->getFieldValue('email')->getValue());

    }

}


