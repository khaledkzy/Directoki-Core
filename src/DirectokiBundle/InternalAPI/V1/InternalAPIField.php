<?php

namespace DirectokiBundle\InternalAPI\V1;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\FieldTypeEmail;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeText;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueEmail;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueLatLng;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueMultiSelect;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueString;
use DirectokiBundle\InternalAPI\V1\Model\FieldValueText;
use DirectokiBundle\InternalAPI\V1\Model\RecordEdit;

use DirectokiBundle\InternalAPI\V1\Model\SelectValue;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class InternalAPIField
{

    protected $container;

    /** @var  Project */
    protected $project;


    /** @var  Directory */
    protected $directory;


    /** @var \DirectokiBundle\Entity\Field */
    protected $field;

    function __construct($container, Project $project, Directory $directory, \DirectokiBundle\Entity\Field $field)
    {
        $this->container = $container;
        $this->project = $project;
        $this->directory = $directory;
        $this->field = $field;
    }


    function getPublishedSelectValues() {

        if ($this->field->getFieldType() != FieldTypeMultiSelect::FIELD_TYPE_INTERNAL) {
            throw new \Exception('Not a Select Field!');
        }

        $out = array();
        $doctrine = $this->container->get('doctrine')->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:SelectValue');
        foreach($repo->findByField($this->field, array('title'=>'ASC')) as $selectValue) {
            $out[] = new SelectValue($selectValue->getPublicId(), $selectValue->getTitle());
        }
        return $out;

    }

    function getPublishedSelectValue(string $valueId) {

        if ($this->field->getFieldType() != FieldTypeMultiSelect::FIELD_TYPE_INTERNAL) {
            throw new \Exception('Not a Select Field!');
        }

        $out = array();
        $doctrine = $this->container->get('doctrine')->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:SelectValue');
        $field = $repo->findOneBy(array('field'=>$this->field, 'publicId'=>$valueId));
        if (!$field) {
            throw new \Exception('Value not found');
        }
        return new SelectValue($field->getPublicId(), $field->getTitle());

    }

}
