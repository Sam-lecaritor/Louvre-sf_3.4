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
        $today = date('Y-m-d');
        $builder
            ->add('dateNaissance', BirthdayType::class, array(
                'widget' => 'single_text',
                               'attr' => [
                    'class' => 'mini-input',
                    'autocomplete' =>'off',
                    'max' => $today,
                    'value' => '1980-01-01'
                ],
                'required'=>true
            ))
            /* ->add('uniqueId') */
            ->add('nom',TextType::class, array(
                'attr' => [
                    'class' => 'mini-input',
                    'autocomplete' =>'off',
                ],
                'label' => 'Nom',
                'required'=>true
            ))
            ->add('prenom',TextType::class, array(
                'attr' => [
                    'class' => 'mini-input',
                    'autocomplete' =>'off',
                ],
                'label' => 'Prénom',
                'required'=>true
            ))
            ->add('pays', CountryType::class, array(
                'preferred_choices' => array('FR', 'GB', 'DE', 'ES', 'IT'),
                'required'=>true
            ))
            ->add('demiJournee', CheckboxType::class, array(
                'label' => '1/2 journée',
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
