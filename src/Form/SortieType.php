<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\CampusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ->add('duree',NumberType::class,[
                'label' => "Durée"
            ])
            ->add('infosSortie',TextareaType::class,[
                'label' => "Description et infos"
            ])
            ->add('campus',EntityType::class,[
                'label' => "Campus",
                'class' => Campus::class,
                'choice_label' => 'nom',
                'choice_value' => 'id'
            ])
            ->add('ville',EntityType::class,[
                'mapped' => false,
                'label' => "Ville",
                'class' => Ville::class,
                'choice_label' => 'nom',
                'choice_value' => 'id'

            ])
            ->add('lieu',EntityType::class,[
                'label' => "Lieu",
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'choice_value' => 'id'

            ])
            ->add('rue',TextType::class,[
                'mapped' => false,
                'label' => "Rue",
                'disabled' => true,

            ])
            ->add('cp',TextType::class,[
                'mapped' => false,
                'label' => "Code Postale",
                'disabled' => true,

            ])
            ->add('latitude',TextType::class,[
                'mapped' => false,
                'label' => "Latitude",
                'disabled' => true,

            ])->add('longitude',TextType::class,[
                'mapped' => false,
                'label' => "Longitude",
                'disabled' => true,
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
