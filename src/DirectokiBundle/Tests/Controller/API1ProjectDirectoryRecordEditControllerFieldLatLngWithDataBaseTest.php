<?php


namespace DirectokiBundle\Tests\Controller;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldLatLngValue;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\Tests\BaseTestWithDataBase;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryRecordEditControllerFieldLatLngWithDataBaseTest extends BaseTestWithDataBase {


    /** If call passes a field when previously there was none, there should be new records created */
    function testLatLngPassChangeNoExisting() {


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
        $field->setTitle('map');
        $field->setPublicId('map');
        $field->setDirectory($directory);
        $field->setFieldType(FieldTypeLatLng::FIELD_TYPE_INTERNAL);
        $field->setCreationEvent($event);
        $this->em->persist($field);


        $record = new Record();
        $record->setDirectory($directory);
        $record->setCreationEvent($event);
        $this->em->persist($record);

        $this->em->flush();

        # TEST


        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldLatLngValue')->findAll();
        $this->assertEquals(0, count($values));

        # CALL API
        $client = $this->container->get('test.client');

        $client->request('POST', '/api1/project/test1/directory/resource/record/' . $record->getPublicId() . '/edit.json', array(
            'field_map_lat' => 55.34,
            'field_map_lng' => 2.34,
            'comment' => 'I make good change!',
            'email' => 'user1@example.com',
        ));

        # TEST AGAIN


        $values = $this->em->getRepository('DirectokiBundle:RecordHasFieldLatLngValue')->findAll();
        $this->assertEquals(1, count($values));

        $value = $values[0];
        $this->assertEquals(55.34, $value->getLat());
        $this->assertEquals(2.34, $value->getLng());
    }

    


}

