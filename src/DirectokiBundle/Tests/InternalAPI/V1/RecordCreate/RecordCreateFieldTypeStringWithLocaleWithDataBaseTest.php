<?php


namespace DirectokiBundle\Tests\InternalAPI\V1\RecordCreate;


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
class RecordCreateFieldTypeStringWithLocaleWithDataBaseTest extends BaseTestWithDataBase
{



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



}
