<?php

namespace DirectokiBundle\Form\Type;

use DirectokiBundle\Entity\RecordHasFieldBooleanValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordHasFieldBooleanValueType extends BaseRecordHasFieldValueType {

    /** @var RecordHasFieldBooleanValue  */
    protected $recordHasBooleanFieldValue;

    function __construct(RecordHasFieldBooleanValue $recordHasBooleanFieldValue ) {
        $this->recordHasBooleanFieldValue = $recordHasBooleanFieldValue;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('value', CheckboxType::class, array(
            'required' => false,
            'label'=>'Value',
            'data' => $this->recordHasBooleanFieldValue->getValue()
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
