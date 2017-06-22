<?php


namespace DirectokiBundle\Tests\InternalAPI\V1;


use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldLatLngValue;
use DirectokiBundle\Entity\RecordHasFieldMultiSelectValue;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasFieldStringWithLocaleValue;
use DirectokiBundle\Entity\RecordHasFieldTextValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeStringWithLocale;
use DirectokiBundle\FieldType\FieldTypeText;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\InternalAPI\V1\InternalAPI;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class GetPublishedRecordsWithDataBaseTest extends BaseTestWithDataBase
{

    public function testString1() {

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
        $field->setTitle( 'Title' );
        $field->setPublicId( 'title' );
        $field->setDirectory( $directory );
        $field->setFieldType( FieldTypeString::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );

        $recordHasFieldStringValue = new RecordHasFieldStringValue();
        $recordHasFieldStringValue->setRecord( $record );
        $recordHasFieldStringValue->setField( $field );
        $recordHasFieldStringValue->setValue( 'My Title Rocks' );
        $recordHasFieldStringValue->setApprovedAt( new \DateTime() );
        $recordHasFieldStringValue->setCreationEvent( $event );
        $this->em->persist( $recordHasFieldStringValue );

        $this->em->flush();

        # TEST, NO CACHE

        $internalAPI = new InternalAPI($this->container);
        $records = $internalAPI->getPublishedRecords("test1","resource");

        $this->assertEquals(1, count($records));
        $this->assertEquals('r1', $records[0]->getPublicId());
        $this->assertNull($records[0]->getFieldValue('title'));


        # CACHE
        $updateRecordCache = new UpdateRecordCache($this->container);
        $updateRecordCache->go($record);

        # TEST, CACHE
        $internalAPI = new InternalAPI($this->container);
        $records = $internalAPI->getPublishedRecords("test1","resource");

        $this->assertEquals(1, count($records));
        $this->assertEquals('r1', $records[0]->getPublicId());
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueString', get_class($records[0]->getFieldValue('title')));
        $this->assertEquals('My Title Rocks', $records[0]->getFieldValue('title')->getValue());

    }

    public function testStringWithLocale1() {

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

        $locale1 = new Locale();
        $locale1->setTitle('en_GB');
        $locale1->setPublicId('en_GB');
        $locale1->setProject($project);
        $locale1->setCreationEvent($event);
        $this->em->persist($locale1);

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
        $field->setTitle( 'Title' );
        $field->setPublicId( 'title' );
        $field->setDirectory( $directory );
        $field->setFieldType( FieldTypeStringWithLocale::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );

        $recordHasFieldStringValue = new RecordHasFieldStringWithLocaleValue();
        $recordHasFieldStringValue->setRecord( $record );
        $recordHasFieldStringValue->setField( $field );
        $recordHasFieldStringValue->setLocale( $locale1);
        $recordHasFieldStringValue->setValue( 'My Title Rocks' );
        $recordHasFieldStringValue->setApprovedAt( new \DateTime() );
        $recordHasFieldStringValue->setCreationEvent( $event );
        $this->em->persist( $recordHasFieldStringValue );

        $this->em->flush();

        # TEST, NO CACHE

        $internalAPI = new InternalAPI($this->container);
        $records = $internalAPI->getPublishedRecords("test1","resource");

        $this->assertEquals(1, count($records));
        $this->assertEquals('r1', $records[0]->getPublicId());
        $this->assertNull($records[0]->getFieldValue('title'));


        # CACHE
        $updateRecordCache = new UpdateRecordCache($this->container);
        $updateRecordCache->go($record);

        # TEST, CACHE
        $internalAPI = new InternalAPI($this->container);
        $records = $internalAPI->getPublishedRecords("test1","resource");

        $this->assertEquals(1, count($records));
        $this->assertEquals('r1', $records[0]->getPublicId());
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueStringWithLocale', get_class($records[0]->getFieldValue('title')));
        $this->assertEquals(true, $records[0]->getFieldValue('title')->haslocale('en_GB'));
        $this->assertEquals('My Title Rocks', $records[0]->getFieldValue('title')->getValue('en_GB'));

    }

    public function testText1() {

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
        $field->setTitle( 'Description' );
        $field->setPublicId( 'description' );
        $field->setDirectory( $directory );
        $field->setFieldType( FieldTypeText::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );

        $recordHasFieldTextValue = new RecordHasFieldTextValue();
        $recordHasFieldTextValue->setRecord( $record );
        $recordHasFieldTextValue->setField( $field );
        $recordHasFieldTextValue->setValue( 'Hairy' );
        $recordHasFieldTextValue->setApprovedAt( new \DateTime() );
        $recordHasFieldTextValue->setCreationEvent( $event );
        $this->em->persist( $recordHasFieldTextValue );

        $this->em->flush();

        # TEST, NO CACHE

        $internalAPI = new InternalAPI($this->container);
        $records = $internalAPI->getPublishedRecords("test1","resource");

        $this->assertEquals(1, count($records));
        $this->assertEquals('r1', $records[0]->getPublicId());
        $this->assertNull($records[0]->getFieldValue('description'));


        # CACHE
        $updateRecordCache = new UpdateRecordCache($this->container);
        $updateRecordCache->go($record);

        # TEST, CACHE
        $internalAPI = new InternalAPI($this->container);
        $records = $internalAPI->getPublishedRecords("test1","resource");

        $this->assertEquals(1, count($records));
        $this->assertEquals('r1', $records[0]->getPublicId());
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueText', get_class($records[0]->getFieldValue('description')));
        $this->assertEquals('Hairy', $records[0]->getFieldValue('description')->getValue());

    }





    public function testLatLng1() {

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
        $field->setTitle( 'Map' );
        $field->setPublicId( 'map' );
        $field->setDirectory( $directory );
        $field->setFieldType( FieldTypeLatLng::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );

        $recordHasFieldLatLngValue = new RecordHasFieldLatLngValue();
        $recordHasFieldLatLngValue->setRecord( $record );
        $recordHasFieldLatLngValue->setField( $field );
        $recordHasFieldLatLngValue->setLat( 2.76 );
        $recordHasFieldLatLngValue->setLng( -3.71 );
        $recordHasFieldLatLngValue->setApprovedAt( new \DateTime() );
        $recordHasFieldLatLngValue->setCreationEvent( $event );
        $this->em->persist( $recordHasFieldLatLngValue );

        $this->em->flush();

        # TEST, NO CACHE

        $internalAPI = new InternalAPI($this->container);
        $records = $internalAPI->getPublishedRecords("test1","resource");

        $this->assertEquals(1, count($records));
        $this->assertEquals('r1', $records[0]->getPublicId());
        $this->assertNull($records[0]->getFieldValue('map'));


        # CACHE
        $updateRecordCache = new UpdateRecordCache($this->container);
        $updateRecordCache->go($record);

        # TEST, CACHE
        $internalAPI = new InternalAPI($this->container);
        $records = $internalAPI->getPublishedRecords("test1","resource");

        $this->assertEquals(1, count($records));
        $this->assertEquals('r1', $records[0]->getPublicId());
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueLatLng', get_class($records[0]->getFieldValue('map')));
        $this->assertEquals(2.76, $records[0]->getFieldValue('map')->getLat());
        $this->assertEquals(-3.71, $records[0]->getFieldValue('map')->getLng());

    }

    public function testMultiSelect1() {

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

        # TEST, NO CACHE

        $internalAPI = new InternalAPI($this->container);
        $records = $internalAPI->getPublishedRecords("test1","resource");

        $this->assertEquals(1, count($records));
        $this->assertEquals('r1', $records[0]->getPublicId());
        $this->assertEquals(0, count($records[0]->getFieldValue('topics')->getSelectValues()));

        # CACHE
        $updateRecordCache = new UpdateRecordCache($this->container);
        $updateRecordCache->go($record);

        # TEST, CACHE
        $internalAPI = new InternalAPI($this->container);
        $records = $internalAPI->getPublishedRecords("test1","resource");

        $this->assertEquals(1, count($records));
        $this->assertEquals('r1', $records[0]->getPublicId());
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueMultiSelect', get_class($records[0]->getFieldValue('topics')));

        $selectValues = $records[0]->getFieldValue('topics')->getSelectValues();
        $this->assertEquals(1, count($selectValues));

        $this->assertEquals('Cats', $selectValues[0]->getTitle());
        $this->assertEquals('cats', $selectValues[0]->getId());

    }




}


