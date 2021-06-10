<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',TextType::class)
            ->add('category',TextType::class)
            ->add('description',TextareaType::class, [
                'required' => false
            ])
            ->add('place',TextType::class)
            ->add('start_time',DateTimeType::class)
            ->add('end_time',DateTimeType::class)
            ->add('access',ChoiceType::class,[
                'choices'  => [
                    'public' => 'Public',
                    'private' => 'Private']
            ])
            ->add('image',FileType::class, array('required' => false,'mapped'=>false))
            ->add('save',SubmitType::class , [
                //add attribute or change them
                'attr' => [
                    'class' => 'btn btn-primary float-right'
                ]
            ])
        ;
        $builder->get('image')->addModelTransformer(new CallBackTransformer(
            function($image) {
                return null;
            },
            function($image) {
                return $image;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'file_uri' => null,
        ]);
    }
}
