<?php


namespace DirectokiBundle\Tests\FieldType;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\Tests\BaseTest;
use DirectokiBundle\Tests\BaseTestWithDataBase;
use JMBTechnology\UserAccountsBundle\Entity\User;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldTypeMultiSelectWithDatabaseTest extends BaseTestWithDataBase
{


    function testParseCSVLineDataTestAddValueId1() {


        $user = new User();
        $user->setEmail( 'test1@example.com' );
        $user->setPassword( 'password' );
        $user->setUsername( 'test1' );
        $this->em->persist( $user );

        $project = new Project();
        $project->setTitle( 'test1' );
        $project->setPublicId( 'test1' );
        $project->setOwner( $user );
        $this->em->persist( $project );

        $event = new Event();
        $event->setProject( $project );
        $event->setUser( $user );
        $this->em->persist( $event );

        $directory = new Directory();
        $directory->setPublicId( 'resource' );
        $directory->setTitleSingular( 'Resource' );
        $directory->setTitlePlural( 'Resources' );
        $directory->setProject( $project );
        $directory->setCreationEvent( $event );
        $this->em->persist( $directory );

        $field = new Field();
        $field->setTitle( 'Topic' );
        $field->setPublicId( 'topic' );
        $field->setDirectory( $directory );
        $field->setFieldType( FieldTypeMultiSelect::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );

        $selectValue = new SelectValue();
        $selectValue->setField($field);
        $selectValue->setTitle('Cats');
        $selectValue->setPublicId('m0w');
        $selectValue->setCreationEvent($event);
        $this->em->persist($selectValue);

        $this->em->flush();


        $fieldConfig = array(
            'add_value_id'=>'m0w',
        );
        $lineData = array(
            'cats',
            '3.4',
            '6.7',
        );
        $record = new Record();
        $event = new Event();
        $publish = false;


        $fieldType = new FieldTypeMultiSelect($this->container);
        $result = $fieldType->parseCSVLineData($field, $fieldConfig, $lineData, $record, $event, $publish);
        $this->assertEquals('DirectokiBundle\ImportCSVLineResult', get_class($result));
        $this->assertEquals('Cats', $result->getDebugOutput());
        $this->assertEquals(1, count($result->getEntitiesToSave()));
        $this->assertEquals("DirectokiBundle\Entity\RecordHasFieldMultiSelectValue", get_class($result->getEntitiesToSave()[0]));
        $this->assertEquals('Cats', $result->getEntitiesToSave()[0]->getSelectValue()->getTitle());
    }



    function testParseCSVLineDataTestAddValueId2() {


        $user = new User();
        $user->setEmail( 'test1@example.com' );
        $user->setPassword( 'password' );
        $user->setUsername( 'test1' );
        $this->em->persist( $user );

        $project = new Project();
        $project->setTitle( 'test1' );
        $project->setPublicId( 'test1' );
        $project->setOwner( $user );
        $this->em->persist( $project );

        $event = new Event();
        $event->setProject( $project );
        $event->setUser( $user );
        $this->em->persist( $event );

        $directory = new Directory();
        $directory->setPublicId( 'resource' );
        $directory->setTitleSingular( 'Resource' );
        $directory->setTitlePlural( 'Resources' );
        $directory->setProject( $project );
        $directory->setCreationEvent( $event );
        $this->em->persist( $directory );

        $field = new Field();
        $field->setTitle( 'Topic' );
        $field->setPublicId( 'topic' );
        $field->setDirectory( $directory );
        $field->setFieldType( FieldTypeMultiSelect::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );

        $selectValue1 = new SelectValue();
        $selectValue1->setField($field);
        $selectValue1->setTitle('Cats');
        $selectValue1->setPublicId('m0w');
        $selectValue1->setCreationEvent($event);
        $this->em->persist($selectValue1);

        $selectValue2 = new SelectValue();
        $selectValue2->setField($field);
        $selectValue2->setTitle('Dogs');
        $selectValue2->setPublicId('w0f');
        $selectValue2->setCreationEvent($event);
        $this->em->persist($selectValue2);

        $this->em->flush();


        $fieldConfig = array(
            'add_value_id'=>'m0w  , ,w0f', // test extra spaces and blank item to
        );
        $lineData = array(
            'cats',
            '3.4',
            '6.7',
        );
        $record = new Record();
        $event = new Event();
        $publish = false;


        $fieldType = new FieldTypeMultiSelect($this->container);
        $result = $fieldType->parseCSVLineData($field, $fieldConfig, $lineData, $record, $event, $publish);
        $this->assertEquals('DirectokiBundle\ImportCSVLineResult', get_class($result));
        $this->assertEquals('Cats, Dogs', $result->getDebugOutput());
        $this->assertEquals(2, count($result->getEntitiesToSave()));
        $this->assertEquals("DirectokiBundle\Entity\RecordHasFieldMultiSelectValue", get_class($result->getEntitiesToSave()[0]));
        $this->assertEquals('Cats', $result->getEntitiesToSave()[0]->getSelectValue()->getTitle());
        $this->assertEquals("DirectokiBundle\Entity\RecordHasFieldMultiSelectValue", get_class($result->getEntitiesToSave()[1]));
        $this->assertEquals('Dogs', $result->getEntitiesToSave()[1]->getSelectValue()->getTitle());
    }

    function testParseCSVLineDataTestAddTitleColumn1() {


        $user = new User();
        $user->setEmail( 'test1@example.com' );
        $user->setPassword( 'password' );
        $user->setUsername( 'test1' );
        $this->em->persist( $user );

        $project = new Project();
        $project->setTitle( 'test1' );
        $project->setPublicId( 'test1' );
        $project->setOwner( $user );
        $this->em->persist( $project );

        $event = new Event();
        $event->setProject( $project );
        $event->setUser( $user );
        $this->em->persist( $event );

        $directory = new Directory();
        $directory->setPublicId( 'resource' );
        $directory->setTitleSingular( 'Resource' );
        $directory->setTitlePlural( 'Resources' );
        $directory->setProject( $project );
        $directory->setCreationEvent( $event );
        $this->em->persist( $directory );

        $field = new Field();
        $field->setTitle( 'Topic' );
        $field->setPublicId( 'topic' );
        $field->setDirectory( $directory );
        $field->setFieldType( FieldTypeMultiSelect::FIELD_TYPE_INTERNAL );
        $field->setCreationEvent( $event );
        $this->em->persist( $field );


        $this->em->flush();


        $fieldConfig = array(
            'add_title_column'=>'1'
        );
        $lineData = array(
            'cats',
            'Test1, Test2, ', // test extra spaces and blank item to
            '6.7',
        );
        $record = new Record();
        $event = new Event();
        $publish = false;


        $fieldType = new FieldTypeMultiSelect($this->container);
        $result = $fieldType->parseCSVLineData($field, $fieldConfig, $lineData, $record, $event, $publish);
        $this->assertEquals('DirectokiBundle\ImportCSVLineResult', get_class($result));
        $this->assertEquals('New Select Value: Test1, Test1, New Select Value: Test2, Test2', $result->getDebugOutput());
        $this->assertEquals(4, count($result->getEntitiesToSave()));
        $this->assertEquals("DirectokiBundle\Entity\SelectValue", get_class($result->getEntitiesToSave()[0]));
        $this->assertEquals('Test1', $result->getEntitiesToSave()[0]->getTitle());
        $this->assertEquals("DirectokiBundle\Entity\RecordHasFieldMultiSelectValue", get_class($result->getEntitiesToSave()[1]));
        $this->assertEquals('Test1', $result->getEntitiesToSave()[1]->getSelectValue()->getTitle());
        $this->assertEquals("DirectokiBundle\Entity\SelectValue", get_class($result->getEntitiesToSave()[2]));
        $this->assertEquals('Test2', $result->getEntitiesToSave()[2]->getTitle());
        $this->assertEquals("DirectokiBundle\Entity\RecordHasFieldMultiSelectValue", get_class($result->getEntitiesToSave()[3]));
        $this->assertEquals('Test2', $result->getEntitiesToSave()[3]->getSelectValue()->getTitle());
    }




}

