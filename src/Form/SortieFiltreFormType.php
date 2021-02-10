<?php

namespace App\Form;

use App\Entity\Sortie;
use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFiltreFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Recherche par Campus
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => function ($campus) {
                    return $campus->getNom();
                },
                'empty_data' => null,
                'placeholder' => '--- Choisir un Campus ---',
            ])
            // Recherche par mot clé
            ->add('nom', SearchType::class, [
                'label' => 'Le nom de la sortie contient',
            ])
            // Recherche par date
            ->add('dateHeureDebut', DateTimeType::class, [
                'attr' => ['class' => 'js-datepicker'],
                'html5' => false,
                'empty_data' => null,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'label' => 'Entre'
            ])

            ->add('dateHeureFin', DateTimeType::class, [
                'attr' => ['class' => 'js-datepicker'],
                'html5' => false,
                'empty_data' => null,
                'mapped' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'label' => 'et'
            ])
            // Filtres
            ->add('sortiesOrganisees', CheckboxType::class, [
                'label'    => 'sorties dont je suis l\'organisateur.trice',
                'required' => false,
                'empty_data' => null,
                'mapped' => false
            ])
            ->add('sortiesInscrit', CheckboxType::class, [
                'label'    => 'sorties auxquelles je suis inscrit.e',
                'required' => false,
                'empty_data' => null,
                'mapped' => false
            ])
            ->add('sortiesNonInscrit', CheckboxType::class, [
                'label'    => 'sorties auxquelles je ne suis pas inscrit.e',
                'required' => false,
                'empty_data' => null,
                'mapped' => false
            ])
            ->add('sortiesPassees',CheckboxType::class,  [
                'label'    => 'sorties passées',
                'required'      => false,
                'empty_data' => null,
                'mapped' => false
            ])
            // Bouton de validation
            ->add('submit', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => ['class' => 'btn btn-warning btn-lg mt-3'],
            ])

            //les form de recherche sont en GET !
            ->setMethod("GET")
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
