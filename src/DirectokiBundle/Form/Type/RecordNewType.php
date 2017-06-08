<?php

namespace DirectokiBundle\Form\Type;

use DirectokiBundle\Entity\DataHasStringField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordNewType extends AbstractType {

    protected $fields;

    protected $container;

    /**
     * RecordNewType constructor.
     * @param $fields
     */
    public function __construct($container, $fields)
    {
        $this->fields = $fields;
        $this->container = $container;
    }


    public function buildForm(FormBuilderInterface $builder, array $options) {


        foreach($this->fields as $field) {

            $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);

            $fieldType->addToNewRecordForm($field, $builder);

        }


        $builder->add('approve',  CheckboxType::class, array(
            'required' => false,
            'label'=>'Approve instantly?',
            'data' =>true,
        ));

    }

    public function getName() {
        return 'tree';
    }

    public function getDefaultOptions(array $options) {
        return array(
        );
    }

}
