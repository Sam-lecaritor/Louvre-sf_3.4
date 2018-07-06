<?php

namespace LouvreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class BilletType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateNaissance', BirthdayType::class, array(
                'widget' => 'single_text',
                               'attr' => [
                    'class' => 'mini-input',
                    'autocomplete' =>'off',
                ],
            ))
            /* ->add('uniqueId') */
            ->add('nom',TextType::class, array(
                'attr' => [
                    'class' => 'mini-input',
                    'autocomplete' =>'off',
                ],
                'label' => 'nom'
            ))
            ->add('prenom',TextType::class, array(
                'attr' => [
                    'class' => 'mini-input',
                    'autocomplete' =>'off',
                ],
                'label' => 'prenom'
            ))
            ->add('pays', CountryType::class, array(
                'preferred_choices' => array('FR', 'GB', 'DE', 'ES'),
            ))
            /*  ->add('tarif') */
            ->add('demiJournee', CheckboxType::class, array(
                'label' => 'Demi-journée',
            ))
            ->add('tarif', CheckboxType::class, array(
                'label' => 'Tarif-reduit',
            ));
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LouvreBundle\Entity\Billet',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'louvrebundle_billet';
    }

}
