<?php

namespace LouvreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BilletsOptionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('nombre', RangeType::class, array(
                'attr' => array(
                    'min' => 1,
                    'max' => 50,
                    'value' => 1,
                )))
            ->add('date', DateType::class, array(
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'datepicker',
                    'autocomplete' => "off",
                    'placeholder' => 'Date de la visite',
                ],
                'format' => "dd-MM-yyyy",
            ))
            ->add('mail', EmailType::class, array(
                'attr' => [
                    'placeholder' => 'Votre mail',
                ],
            ))
            ->add('Valider', SubmitType::class, array(
                'attr' => [
                    'class' => 'btn-success margin-top-half',
                ],
            ));

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LouvreBundle\Entity\BilletsOption',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'louvrebundle_billetsoption';
    }

}
