<?php

namespace DirectokiBundle\InternalAPI\V1\Model;
use JMBTechnology\UserAccountsBundle\Entity\User;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordEdit extends Record {

    protected $fieldsEdit = array();

    protected $comment;

    protected $email;

    protected $user;


    public function __construct(Record $record) {

        $this->projectPublicId = $record->projectPublicId;
        $this->directoryPublicId = $record->directoryPublicId;
        $this->publicID = $record->publicID;
        $this->fields = $record->fields;
        foreach($record->fields as $field) {
            if ($field instanceof FieldValueString) {
                $this->fieldsEdit[$field->getPublicID()] = new FieldValueStringEdit($field);
            } else if ($field instanceof FieldValueText) {
                $this->fieldsEdit[$field->getPublicID()] = new FieldValueTextEdit($field);
            } else if ($field instanceof FieldValueEmail) {
                $this->fieldsEdit[$field->getPublicID()] = new FieldValueEmailEdit($field);
            } else if ($field instanceof FieldValueLatLng) {
                $this->fieldsEdit[$field->getPublicID()] = new FieldValueLatLngEdit($field);
            }
        }
    }


    public function getFieldValueEdit($pubicId) {
        return isset($this->fieldsEdit[$pubicId]) ? $this->fieldsEdit[$pubicId] : null;
    }

    public function getFieldValueEdits() {
        return $this->fieldsEdit;
    }

    /**
     * @return mixed
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     */
    public function setComment( $comment ) {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail( $email ) {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser(User  $user ) {
        $this->user = $user;
    }



}
