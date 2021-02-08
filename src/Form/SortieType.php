<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom',TextType::class,[
                'label' => "Nom de la Sortie",
                'attr' => [
                    'class' => "form-control"
                ]

            ])
            ->add('dateHeureDebut',DateTimeType::class,[
                'label' => "Date et heure de la sortie"
            ])
            ->add('dateLimiteInscription',DateType::class,[
                'label' => "Date limite d'inscription"
            ])
            ->add('nbInscriptionsMax',NumberType::class,[
                'label' => "Nombre de places"
            ])
            ->add('duree',TimeType::class,[
                'label' => "Durée"
            ])
            ->add('infosSortie',TextareaType::class,[
                'label' => "Description et infos"
            ])
            ->add('campus',ChoiceType::class,[
                'label' => "Campus"
            ])
            ->add('ville',ChoiceType::class,[
                'label' => "Ville"
            ])
            ->add('lieu',ChoiceType::class,[
                'label' => "Lieu"
            ])
            ->add('cp',ChoiceType::class,[
                'label' => "Code Postale"
            ])->add('latitude',ChoiceType::class,[
                'label' => "Latitude"
            ])->add('longitude',ChoiceType::class,[
                'label' => "Longitude"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
