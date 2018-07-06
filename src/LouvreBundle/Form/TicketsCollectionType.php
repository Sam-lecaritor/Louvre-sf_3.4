<?php

namespace LouvreBundle\Form;

use LouvreBundle\Form\BilletType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketsCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        /*  ->add('clientId') */
            ->add('billets', CollectionType::class, [
                'label' => 'Ajouter les informations pour chacun des billets souhaités.',
                'entry_type' => BilletType::class,
                'entry_options' => [
                    'attr' => [
                        'class' => 'item', // we want to use 'tr.item' as collection elements' selector
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'required' => false,
                'by_reference' => true,
                'delete_empty' => true,
                'attr' => [
                    'class' => 'table billet-collection',
                ],

            ])
            ->add('save', SubmitType::class, [
                'label' => 'valider',
                'attr' => [
                    'class' => 'btn-success',
                ],
            ])
/*         ->add('clientMail')
->add('prixTotal')
->add('confirmed') */;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LouvreBundle\Entity\TicketsCollection',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'louvrebundle_ticketscollection';
    }

}
