<?php


namespace DirectokiBundle\Tests\InternalAPI\V1;


use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
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
class PublishedCreateWithDataBaseTest extends BaseTestWithDataBase {



    public function testBlankCreate() {

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

        $field = new Field();
        $field->setTitle('Title');
        $field->setPublicId('title');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeString::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $field = new Field();
        $field->setTitle('Description');
        $field->setPublicId('description');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeText::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);


        // TODO add one of each field type here

        $this->em->flush();



        # CREATE
        $internalAPI = new InternalAPI($this->container);

        $internalAPIDirectory = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource');

        $recordCreate = $internalAPIDirectory->getRecordCreate();
        // Don't set any field values! We should be smart enough not to save.
        $recordCreate->setComment('Test');
        $recordCreate->setEmail('test@example.com');
        $recordCreate->setApproveInstantlyIfAllowed(false);

        $this->assertFalse($internalAPIDirectory->saveRecordCreate($recordCreate));




        # TEST

        $records = $this->em->getRepository('DirectokiBundle:Record')->getRecordsNeedingAttention($directory);
        $this->assertEquals(0, count($records));


    }



    public function testStringWithLocaleField()
    {

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

        $field = new Field();
        $field->setTitle('Title');
        $field->setPublicId('title');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeStringWithLocale::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);


        $this->em->flush();


        # CREATE
        $internalAPI = new InternalAPI($this->container);

        $internalAPIDirectory = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource');

        $recordCreate = $internalAPIDirectory->getRecordCreate();
        $recordCreate->getFieldValueEdit('title')->setNewValue('en_GB','A Title');
        $recordCreate->setComment('Test');
        $recordCreate->setEmail('test@example.com');
        $recordCreate->setApproveInstantlyIfAllowed(false);

        $this->assertTrue($internalAPIDirectory->saveRecordCreate($recordCreate));


        # TEST

        $records = $this->em->getRepository('DirectokiBundle:Record')->getRecordsNeedingAttention($directory);
        $this->assertEquals(1, count($records));

        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);

        $fieldRecords = $this->em->getRepository('DirectokiBundle:RecordHasFieldStringWithLocaleValue')->findAll();

        $this->assertEquals(1, count($fieldRecords));

        $fieldRecord = $fieldRecords[0];

        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldStringWithLocaleValue', get_class($fieldRecord));
        $this->assertEquals('A Title', $fieldRecord->getValue());


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

        $field = new Field();
        $field->setTitle('Description');
        $field->setPublicId('description');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeText::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);


        $this->em->flush();



        # CREATE
        $internalAPI = new InternalAPI($this->container);

        $internalAPIDirectory = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource');

        $recordCreate = $internalAPIDirectory->getRecordCreate();
        $recordCreate->getFieldValueEdit('description')->setNewValue('I can count.');
        $recordCreate->setComment('Test');
        $recordCreate->setEmail('test@example.com');
        $recordCreate->setApproveInstantlyIfAllowed(false);

        $this->assertTrue($internalAPIDirectory->saveRecordCreate($recordCreate));




         # TEST

        $records = $this->em->getRepository('DirectokiBundle:Record')->getRecordsNeedingAttention($directory);
        $this->assertEquals(1, count($records));

        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);

        $fieldModerationsNeeded = $fieldType->getFieldValuesToModerate($field, $records[0]);

        $this->assertEquals(1, count($fieldModerationsNeeded));

        $fieldModerationNeeded = $fieldModerationsNeeded[0];

        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldTextValue', get_class($fieldModerationNeeded));
        $this->assertEquals('I can count.', $fieldModerationNeeded->getValue());




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

        $field = new Field();
        $field->setTitle('Map');
        $field->setPublicId('map');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeLatLng::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);


        $this->em->flush();



        # CREATE
        $internalAPI = new InternalAPI($this->container);

        $internalAPIDirectory = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource');

        $recordCreate = $internalAPIDirectory->getRecordCreate();
        $recordCreate->getFieldValueEdit('map')->setNewLat(7.89);
        $recordCreate->getFieldValueEdit('map')->setNewLng(-2.83);
        $recordCreate->setComment('Test');
        $recordCreate->setEmail('test@example.com');
        $recordCreate->setApproveInstantlyIfAllowed(false);

        $this->assertTrue($internalAPIDirectory->saveRecordCreate($recordCreate));




        # TEST

        $records = $this->em->getRepository('DirectokiBundle:Record')->getRecordsNeedingAttention($directory);
        $this->assertEquals(1, count($records));

        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);

        $fieldModerationsNeeded = $fieldType->getFieldValuesToModerate($field, $records[0]);

        $this->assertEquals(1, count($fieldModerationsNeeded));

        $fieldModerationNeeded = $fieldModerationsNeeded[0];

        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldLatLngValue', get_class($fieldModerationNeeded));
        $this->assertEquals(7.89, $fieldModerationNeeded->getLat());
        $this->assertEquals(-2.83, $fieldModerationNeeded->getLng());


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

        $field = new Field();
        $field->setTitle('Email');
        $field->setPublicId('email');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeEmail::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);


        $this->em->flush();



        # CREATE
        $internalAPI = new InternalAPI($this->container);

        $internalAPIDirectory = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource');

        $recordCreate = $internalAPIDirectory->getRecordCreate();
        $recordCreate->getFieldValueEdit('email')->setNewValue('bob@example.com');
        $recordCreate->setComment('Test');
        $recordCreate->setEmail('test@example.com');
        $recordCreate->setApproveInstantlyIfAllowed(false);

        $this->assertTrue($internalAPIDirectory->saveRecordCreate($recordCreate));




        # TEST

        $records = $this->em->getRepository('DirectokiBundle:Record')->getRecordsNeedingAttention($directory);
        $this->assertEquals(1, count($records));

        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);

        $fieldModerationsNeeded = $fieldType->getFieldValuesToModerate($field, $records[0]);

        $this->assertEquals(1, count($fieldModerationsNeeded));

        $fieldModerationNeeded = $fieldModerationsNeeded[0];

        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldEmailValue', get_class($fieldModerationNeeded));
        $this->assertEquals('bob@example.com', $fieldModerationNeeded->getValue());


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

        $field = new Field();
        $field->setTitle('Tags');
        $field->setPublicId('tags');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeMultiSelect::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);


        $selectValue = new SelectValue();
        $selectValue->setField($field);
        $selectValue->setCreationEvent($event);
        $selectValue->setTitle('PHP');
        $this->em->persist($selectValue);

        $this->em->flush();


        # CREATE
        $internalAPI = new InternalAPI($this->container);

        $internalAPIDirectory = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource');

        $selectValuesFromAPI = $internalAPIDirectory->getFieldAPI('tags')->getPublishedSelectValues();
        $this->assertEquals(1, count($selectValuesFromAPI));
        $this->assertEquals('PHP', $selectValuesFromAPI[0]->getTitle());


        $recordCreate = $internalAPIDirectory->getRecordCreate();
        $recordCreate->getFieldValueEdit('tags')->addValueToAdd($selectValuesFromAPI[0]);
        $recordCreate->setComment('Test');
        $recordCreate->setEmail('test@example.com');
        $recordCreate->setApproveInstantlyIfAllowed(false);

        $this->assertTrue($internalAPIDirectory->saveRecordCreate($recordCreate));


        # TEST

        $records = $this->em->getRepository('DirectokiBundle:Record')->getRecordsNeedingAttention($directory);
        $this->assertEquals(1, count($records));

        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);

        $fieldModerationsNeeded = $fieldType->getModerationsNeeded($field, $records[0]);

        $this->assertEquals(1, count($fieldModerationsNeeded));

        $fieldModerationNeeded = $fieldModerationsNeeded[0];

        $this->assertEquals('DirectokiBundle\ModerationNeeded\ModerationNeededRecordHasFieldMultiValueAddition', get_class($fieldModerationNeeded));
        $this->assertEquals('PHP', $fieldModerationNeeded->getFieldValue()->getSelectValue()->getTitle());


    }


}

