<?php

namespace DirectokiBundle\Form\Type;

use DirectokiBundle\Entity\RecordHasFieldStringValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordHasFieldStringValueType extends BaseRecordHasFieldValueType {

    /** @var RecordHasFieldStringValue  */
    protected $recordHasStringFieldValue;

    function __construct(RecordHasFieldStringValue $recordHasStringFieldValue ) {
        $this->recordHasStringFieldValue = $recordHasStringFieldValue;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {


        $builder->add('value', 'text', array(
            'required' => false,
            'label'=>'Value',
            'data' => $this->recordHasStringFieldValue->getValue()
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
