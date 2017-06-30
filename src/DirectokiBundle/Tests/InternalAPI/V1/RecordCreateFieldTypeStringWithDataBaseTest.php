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
class PublishedCreateFieldTypeStringWithDataBaseTest extends BaseTestWithDataBase {

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

        $field = new Field();
        $field->setTitle('Title');
        $field->setPublicId('title');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeString::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);


        $this->em->flush();



        # CREATE
        $internalAPI = new InternalAPI($this->container);
        $internalAPIDirectory = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource');

        $recordCreate = $internalAPIDirectory->getRecordCreate();
        $recordCreate->getFieldValueEdit('title')->setNewValue('A Title');
        $recordCreate->setComment('Test');
        $recordCreate->setEmail('test@example.com');

        $this->assertTrue($internalAPIDirectory->saveRecordCreate($recordCreate));




         # TEST

        $records = $this->em->getRepository('DirectokiBundle:Record')->getRecordsNeedingAttention($directory);
        $this->assertEquals(1, count($records));

        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);

        $fieldModerationsNeeded = $fieldType->getFieldValuesToModerate($field, $records[0]);

        $this->assertEquals(1, count($fieldModerationsNeeded));

        $fieldModerationNeeded = $fieldModerationsNeeded[0];

        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldStringValue', get_class($fieldModerationNeeded));
        $this->assertEquals('A Title', $fieldModerationNeeded->getValue());


    }






    public function testApproveInstantlyIfAllowedWhenNotAllowed() {

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


        $this->em->flush();



        # CREATE
        $internalAPI = new InternalAPI($this->container);

        $internalAPIDirectory = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource');

        $recordCreate = $internalAPIDirectory->getRecordCreate();
        $recordCreate->getFieldValueEdit('title')->setNewValue('A Title');
        $recordCreate->setComment('Test');
        $recordCreate->setEmail('test@example.com');
        $recordCreate->setApproveInstantlyIfAllowed(true);

        $this->assertTrue($internalAPIDirectory->saveRecordCreate($recordCreate));




        # TEST

        $records = $this->em->getRepository('DirectokiBundle:Record')->getRecordsNeedingAttention($directory);
        $this->assertEquals(1, count($records));

        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);

        $fieldModerationsNeeded = $fieldType->getFieldValuesToModerate($field, $records[0]);

        $this->assertEquals(1, count($fieldModerationsNeeded));

        $fieldModerationNeeded = $fieldModerationsNeeded[0];

        $this->assertEquals('DirectokiBundle\Entity\RecordHasFieldStringValue', get_class($fieldModerationNeeded));
        $this->assertEquals('A Title', $fieldModerationNeeded->getValue());


    }



    public function testApproveInstantlyIfAllowedWhenAllowed() {

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


        $this->em->flush();



        # CREATE
        $internalAPI = new InternalAPI($this->container);
        $internalAPIDirectory = $internalAPI->getProjectAPI('test1')->getDirectoryAPI('resource');

        $recordCreate = $internalAPIDirectory->getRecordCreate();
        $recordCreate->getFieldValueEdit('title')->setNewValue('A Title');
        $recordCreate->setComment('Test');
        $recordCreate->setUser($user);
        $recordCreate->setApproveInstantlyIfAllowed(true);

        $this->assertTrue($internalAPIDirectory->saveRecordCreate($recordCreate));




        # TEST

        $recordsNeedingAttention = $this->em->getRepository('DirectokiBundle:Record')->getRecordsNeedingAttention($directory);
        $this->assertEquals(0, count($recordsNeedingAttention));

        $records = $this->em->getRepository('DirectokiBundle:Record')->findByDirectory($directory);
        $this->assertEquals(1, count($records));

        $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
        $this->assertEquals('A Title', $fieldType->getLatestFieldValues($field, $records[0])[0]->getValue());



    }


}

