<?php

namespace DirectokiBundle\Form\Type;

use DirectokiBundle\Entity\RecordHasFieldLatLngValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordHasFieldLatLngValueType extends BaseRecordHasFieldValueType {

    /** @var RecordHasFieldLatLngValue  */
    protected $recordHasLatLngFieldValue;

    function __construct(RecordHasFieldLatLngValue $recordHasLatLngFieldValue ) {
        $this->recordHasLatLngFieldValue = $recordHasLatLngFieldValue;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {


        $builder->add('lat', NumberType::class, array(
            'required' => false,
            'label'=>'Lat',
            'scale'=>12,
            'data' => $this->recordHasLatLngFieldValue->getLat()
        ));

        $builder->add('lng', NumberType::class, array(
            'required' => false,
            'label'=>'Lng',
            'scale'=>12,
            'data' => $this->recordHasLatLngFieldValue->getLng()
        ));


        parent::buildForm($builder, $options);

    }

    public function getName() {
        return 'latlng';
    }

    public function getDefaultOptions(array $options) {
        return array(
        );
    }

}
