<?php

namespace DirectokiBundle\Form\Type;

use DirectokiBundle\Entity\DataHasStringField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class SelectValueNewType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {


        $builder->add('title', TextType::class, array(
            'required' => true,
            'label'=>'Title',
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
