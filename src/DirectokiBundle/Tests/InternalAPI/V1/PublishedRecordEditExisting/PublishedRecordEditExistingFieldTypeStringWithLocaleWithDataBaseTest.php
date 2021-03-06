<?php


namespace DirectokiBundle\Tests\InternalAPI\V1\PublishedRecordEditExisting;


use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldEmailValue;
use DirectokiBundle\Entity\RecordHasFieldLatLngValue;
use DirectokiBundle\Entity\RecordHasFieldMultiSelectValue;
use DirectokiBundle\Entity\RecordHasFieldStringValue;
use DirectokiBundle\Entity\RecordHasFieldStringWithLocaleValue;
use DirectokiBundle\Entity\RecordHasFieldTextValue;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\FieldType\FieldTypeEmail;
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
class PublishedRecordEditExistingFieldTypeStringWithLocaleWithDataBaseTest extends BaseTestWithDataBase
{



    public function testStringWithLocaleField() {

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
        $field->setTitle('Title');
        $field->setPublicId('title');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeStringWithLocale::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $recordHasFieldStringWithLocaleValue = new RecordHasFieldStringWithLocaleValue();
        $recordHasFieldStringWithLocaleValue->setRecord($record);
        $recordHasFieldStringWithLocaleValue->setField($field);
        $recordHasFieldStringWithLocaleValue->setLocale($locale1);
        $recordHasFieldStringWithLocaleValue->setValue('My Title Rocks');
        $recordHasFieldStringWithLocaleValue->setApprovedAt(new \DateTime());
        $recordHasFieldStringWithLocaleValue->setCreationEvent($event);
        $this->em->persist($recordHasFieldStringWithLocaleValue);

        $this->em->flush();

        # TEST

        $internalAPI = new InternalAPI($this->container);
        $internalAPIRecord = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource')->getRecordAPI('r1');
        $recordEditIntAPI = $internalAPIRecord->getPublishedEdit();

        $this->assertEquals('r1', $recordEditIntAPI->getPublicId());
        $this->assertNotNull($recordEditIntAPI->getFieldValueEdit('title'));
        $this->assertEquals('DirectokiBundle\InternalAPI\V1\Model\FieldValueStringWithLocaleEdit', get_class($recordEditIntAPI->getFieldValueEdit('title')));
        $this->assertEquals('Title', $recordEditIntAPI->getFieldValueEdit('title')->getTitle());
        $this->assertEquals('My Title Rocks', $recordEditIntAPI->getFieldValueEdit('title')->getValue('en_GB'));

        $records = $this->em->getRepository('DirectokiBundle:Record')->getRecordsNeedingAttention($directory);
        $this->assertEquals(0, count($records));


        # Edit

        $recordEditIntAPI->getFieldValueEdit('title')->setNewValue('en_GB' , 'Less Silly Title Please');
        $recordEditIntAPI->setComment('Test');
        $recordEditIntAPI->setEmail('test@example.com');
        $recordEditIntAPI->setApproveInstantlyIfAllowed(false);

        $result = $internalAPIRecord->savePublishedEdit($recordEditIntAPI);
        $this->assertTrue($result->getSuccess());
        $this->assertFalse($result->isApproved());




        # TEST

        $records = $this->em->getRepository('DirectokiBundle:Record')->getRecordsNeedingAttention($directory);
        $this->assertEquals(1, count($records));


        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);



        $fieldModerationsNeeded = $fieldType->getModerationsNeeded($field, $record);



        $this->assertEquals(1, count($fieldModerationsNeeded));

        $fieldModerationNeeded = $fieldModerationsNeeded[0];

        $this->assertEquals('DirectokiBundle\ModerationNeeded\ModerationNeededRecordHasFieldValue', get_class($fieldModerationNeeded));
        $this->assertEquals('Less Silly Title Please', $fieldModerationNeeded->getFieldValue()->getValue());


    }


}