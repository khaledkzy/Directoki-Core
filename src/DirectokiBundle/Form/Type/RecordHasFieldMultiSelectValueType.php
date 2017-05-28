<?php

namespace DirectokiBundle\Form\Type;

use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Record;
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
class RecordHasFieldMultiSelectValueType extends BaseRecordHasFieldValueType {

    protected $selectValues = array();

    protected $selectValuesCurrentValue = array();

    function __construct($container, Field $field, Record $record) {
        $repoSelectValue = $container->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');
        $repoRecordHasFieldMultiSelectValue = $container->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue');

        foreach($repoSelectValue->findBy(array('field'=>$field), array('title'=>'asc')) as $selectValue) {
            $this->selectValues[] = $selectValue;
            $this->selectValuesCurrentValue[$selectValue->getPublicId()] = $repoRecordHasFieldMultiSelectValue->doesRecordHaveFieldHaveValue($record, $field, $selectValue);
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {


        foreach($this->selectValues as $selectValue) {

            $builder->add('value_'. $selectValue->getPublicId(), CheckboxType::class, array(
                'required' => false,
                'label'=>$selectValue->getTitle(),
                'data' =>$this->selectValuesCurrentValue[$selectValue->getPublicId()],
            ));

        }

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
