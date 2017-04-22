<?php


namespace DirectokiBundle\Tests\InternalAPI\V1;


use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasFieldTextValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeText;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\InternalAPI\V1\InternalAPI;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class PublishedRecordEditExistingWithDataBaseTest extends BaseTestWithDataBase {



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
        $recordIntAPI = $internalAPI->getPublishedRecord("test1","resource","r1");
        $recordEditIntAPI = $internalAPI->getPublishedRecordEdit($recordIntAPI);

        $this->assertEquals('r1', $recordEditIntAPI->getPublicId());
        $this->assertNotNull($recordEditIntAPI->getFieldValueEdit('title'));
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueStringEdit', get_class($recordEditIntAPI->getFieldValueEdit('title')));
        $this->assertEquals('Title', $recordEditIntAPI->getFieldValueEdit('title')->getTitle());
        $this->assertEquals('My Title Rocks', $recordEditIntAPI->getFieldValueEdit('title')->getValue());

        $records = $this->em->getRepository('DirectokiBundle:Record')->getRecordsNeedingAttention($directory);
        $this->assertEquals(0, count($records));


        # Edit

        $recordEditIntAPI->getFieldValueEdit('title')->setNewValue('Less Silly Title Please');
        $recordEditIntAPI->setComment('Test');
        $recordEditIntAPI->setEmail('test@example.com');

        $this->assertTrue($internalAPI->savePublishedRecordEdit($recordEditIntAPI));




         # TEST

        $records = $this->em->getRepository('DirectokiBundle:Record')->getRecordsNeedingAttention($directory);
        $this->assertEquals(1, count($records));


        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);



        $fieldModerationsNeeded = $fieldType->getFieldValuesToModerate($field, $record);



        $this->assertEquals(1, count($fieldModerationsNeeded));

        $fieldModerationNeeded = $fieldModerationsNeeded[0];

        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldStringValue', get_class($fieldModerationNeeded));
        $this->assertEquals('Less Silly Title Please', $fieldModerationNeeded->getValue());


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
        $recordHasFieldTextValue->setValue('123');
        $recordHasFieldTextValue->setApprovedAt(new \DateTime());
        $recordHasFieldTextValue->setCreationEvent($event);
        $this->em->persist($recordHasFieldTextValue);

        $this->em->flush();

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $recordIntAPI = $internalAPI->getPublishedRecord("test1","resource","r1");
        $recordEditIntAPI = $internalAPI->getPublishedRecordEdit($recordIntAPI);

        $this->assertEquals('r1', $recordEditIntAPI->getPublicId());
        $this->assertNotNull($recordEditIntAPI->getFieldValueEdit('description'));
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueTextEdit', get_class($recordEditIntAPI->getFieldValueEdit('description')));
        $this->assertEquals('Description', $recordEditIntAPI->getFieldValueEdit('description')->getTitle());
        $this->assertEquals('123', $recordEditIntAPI->getFieldValueEdit('description')->getValue());

        $records = $this->em->getRepository('DirectokiBundle:Record')->getRecordsNeedingAttention($directory);
        $this->assertEquals(0, count($records));


        # Edit

        $recordEditIntAPI->getFieldValueEdit('description')->setNewValue('1, 2, 3.');
        $recordEditIntAPI->setComment('Test');
        $recordEditIntAPI->setEmail('test@example.com');

        $this->assertTrue($internalAPI->savePublishedRecordEdit($recordEditIntAPI));




         # TEST

        $records = $this->em->getRepository('DirectokiBundle:Record')->getRecordsNeedingAttention($directory);
        $this->assertEquals(1, count($records));


        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);



        $fieldModerationsNeeded = $fieldType->getFieldValuesToModerate($field, $record);



        $this->assertEquals(1, count($fieldModerationsNeeded));

        $fieldModerationNeeded = $fieldModerationsNeeded[0];

        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldTextValue', get_class($fieldModerationNeeded));
        $this->assertEquals('1, 2, 3.', $fieldModerationNeeded->getValue());


    }




}

