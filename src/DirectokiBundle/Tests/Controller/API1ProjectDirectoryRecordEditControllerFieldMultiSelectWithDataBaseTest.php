<?php


namespace DirectokiBundle\Tests\Controller;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldMultiSelectValue;
use DirectokiBundle\Entity\RecordHasFieldURLValue;
use DirectokiBundle\Entity\SelectValue;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeURL;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryRecordEditControllerFieldMultiSelectWithDataBaseTest extends BaseTestWithDataBase {


    function testURLPassOneValueNoExisting() {


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
        $field->setTitle('Tech Used');
        $field->setPublicId('tech');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeMultiSelect::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $selectValue = new SelectValue();
        $selectValue->setField($field);
        $selectValue->setCreationEvent($event);
        $selectValue->setTitle('PHP');
        $this->em->persist($selectValue);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);

        $this->em->flush();

        # TEST


        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue')->findAll();
        $this->assertEquals(0, count($values));

        # CALL API
        $client = $this->container->get('test.client');

        $client->request('POST', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/edit.json', array(
            'field_tech_add_title' => 'php ', // passing different case and extra space on purpose
            'comment' => 'I make good change!',
            'email' => 'user1@example.com',
        ));

        # TEST AGAIN


        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue')->findAll();
        $this->assertEquals(1, count($values));

        $value = $values[0];
        $this->assertEquals("PHP", $value->getSelectValue()->getTitle());
    }




    function testURLRemovePassOneID() {


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
        $field->setTitle('Tech Used');
        $field->setPublicId('tech');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeMultiSelect::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $selectValue = new SelectValue();
        $selectValue->setField($field);
        $selectValue->setCreationEvent($event);
        $selectValue->setTitle('PHP');
        $this->em->persist($selectValue);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);

        $recordHasMultiSelectValue = new RecordHasFieldMultiSelectValue();
        $recordHasMultiSelectValue->setRecord($record);
        $recordHasMultiSelectValue->setField($field);
        $recordHasMultiSelectValue->setSelectValue($selectValue);
        $recordHasMultiSelectValue->setAdditionCreationEvent($event);
        $recordHasMultiSelectValue->setAdditionApprovedAt(new \DateTime());
        $recordHasMultiSelectValue->setAdditionApprovalEvent($event);
        $this->em->persist($recordHasMultiSelectValue);

        $this->em->flush();

        # TEST

        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue')->findAll();
        $this->assertEquals(1, count($values));

        $value = $values[0];
        $this->assertEquals("PHP", $value->getSelectValue()->getTitle());
        $this->assertNull($value->getRemovalCreatedAt());

        # CALL API
        $client = $this->container->get('test.client');

        $client->request('POST', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/edit.json', array(
            'field_tech_remove_id' => $selectValue->getPublicId(),
            'comment' => 'I make good change!',
            'email' => 'user1@example.com',
        ));

        # TEST AGAIN

        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue')->findAll();
        $this->assertEquals(1, count($values));

        $value = $values[0];
        $this->assertEquals("PHP", $value->getSelectValue()->getTitle());
        $this->assertNotNull($value->getRemovalCreatedAt());

    }



}

