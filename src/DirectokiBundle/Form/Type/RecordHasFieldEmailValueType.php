<?php

namespace DirectokiBundle\Form\Type;

use DirectokiBundle\Entity\RecordHasFieldEmailValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordHasFieldEmailValueType extends BaseRecordHasFieldValueType {

    /** @var RecordHasFieldEmailValue  */
    protected $recordHasEmailFieldValue;

    function __construct(RecordHasFieldEmailValue $recordHasEmailFieldValue ) {
        $this->recordHasEmailFieldValue = $recordHasEmailFieldValue;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {


        $builder->add('value', EmailType::class, array(
            'required' => false,
            'label'=>'Value',
            'data' => $this->recordHasEmailFieldValue->getValue()
        ));


        parent::buildForm($builder, $options);


    }

    public function getName() {
        return 'tree';
    }

    public function getDefaultOptions(array $options) {
        return array(
        );
    }

}
