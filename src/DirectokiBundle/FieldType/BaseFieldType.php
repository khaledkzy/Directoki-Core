<?php

namespace DirectokiBundle\FieldType;


use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasStringField;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\User;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 *
 */
abstract class  BaseFieldType {


    const FIELD_TYPE_INTERNAL = 'abstract';
    const FIELD_TYPE_API1 = 'abstract';


    protected $container;

    function __construct( $container ) {
        $this->container = $container;
    }

    public abstract function isMultipleType();

    public abstract function getLabel();

    public abstract function getLatestFieldValues(Field $field, Record $record);

    /**
     * @TODO The plan is, the results from this will in future move to getModerationsNeeded() and this method will be removed.
     */
    public abstract function getFieldValuesToModerate(Field $field, Record $record);

    public abstract function getModerationsNeeded(Field $field, Record $record);

    public abstract function getEditFieldForm(Field $field, Record $record);

    public abstract function getEditFieldFormNewRecords(Field $field, Record $record, Event $event, $form, User $user = null, $approve=false);

    public abstract function getViewTemplate();

    public abstract function getAPIJSON(Field $field, Record $record);

    public abstract function processAPI1Record(Field $field, Record $record, ParameterBag $parameterBag, Event $event);

    /**
     * @return ImportCSVLineResult|null
     */
    public abstract function parseCSVLineData(Field $field, $fieldConfig, $lineData,  Record $record, Event $creationEvent, $published=false);

}
