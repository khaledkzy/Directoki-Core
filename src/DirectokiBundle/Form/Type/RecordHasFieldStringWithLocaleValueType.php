<?php

namespace DirectokiBundle\Form\Type;

use DirectokiBundle\Entity\RecordHasFieldStringValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordHasFieldStringWithLocaleValueType extends BaseRecordHasFieldValueType {


    protected $locales;

    protected $values;

    function __construct($locales, $values) {
        $this->locales = $locales;
        $this->values = $values;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        foreach($this->locales as $locale) {

            $builder->add('value_'.$locale->getPublicId(), TextType::class, array(
                'required' => false,
                'label' => 'Value ('.$locale->getTitle().')',
                'data' => $this->values[$locale->getPublicId()],
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
