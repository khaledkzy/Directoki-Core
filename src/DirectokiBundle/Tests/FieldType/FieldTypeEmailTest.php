<?php


namespace DirectokiBundle\Tests\FieldType;

use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\FieldType\FieldTypeEmail;
use DirectokiBundle\Tests\BaseTest;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class FieldTypeEmailTest extends BaseTest
{

    function testParseCSVLineDataTest1() {
        $field = new Field();
        $fieldConfig = array(
            'column'=>0,
        );
        $lineData = array(
            'cats@example.com',
            'dogs@example.com'
        );
        $record = new Record();
        $event = new Event();
        $publish = false;
        $fieldType = new FieldTypeEmail($this->container);
        $result = $fieldType->parseCSVLineData($field, $fieldConfig, $lineData, $record, $event, $publish);
        $this->assertEquals('cats@example.com', $result->getDebugOutput());
        $this->assertEquals(1, count($result->getFieldValuesToSave()));
        $this->assertEquals("DirectokiBundle\Entity\RecordHasFieldEmailValue", get_class($result->getFieldValuesToSave()[0]));
        $this->assertEquals('cats@example.com', $result->getFieldValuesToSave()[0]->getValue());
    }

}
