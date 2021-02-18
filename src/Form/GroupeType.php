<?php

namespace App\Form;

use App\Entity\Groupe;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Nom', TextType :: class, [
                    'required' => true
            ])
            ->add('Membres', EntityType::class,[
                'class' => Participant::class,
                'choice_label' => function(Participant $participant){
                    $choice_label = $participant->getNom() . ' ' . $participant->getPrenom();
                    return $choice_label;
                },
                'choice_value' => 'id',
                'multiple' => true,
                'expanded' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Groupe::class,
            'validation_groups' => ['Default', 'GroupeType']
        ]);
    }
}
