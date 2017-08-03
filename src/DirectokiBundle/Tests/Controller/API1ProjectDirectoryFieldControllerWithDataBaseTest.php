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
use DirectokiBundle\FieldType\FieldTypeString;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeURL;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryFieldControllerWithDataBaseTest extends BaseTestWithDataBase {


    function testGetIndex() {


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
        $field->setFieldType(FieldTypeString::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);

        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);

        $this->em->flush();

        # CALL API
        $client = $this->container->get('test.client');

        $client->request('GET', '/api1/project/test1/directory/resource/field/tech/index.json');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals('tech', $data['field']['id']);
        $this->assertEquals('Tech Used', $data['field']['title']);

    }


    function testGetSelectValues() {


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

        # CALL API
        $client = $this->container->get('test.client');

        $client->request('GET', '/api1/project/test1/directory/resource/field/tech/selectValues.json');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals('tech', $data['field']['id']);
        $this->assertEquals('Tech Used', $data['field']['title']);

        $this->assertEquals('PHP', $data['select_values'][0]['title']);


    }





}

