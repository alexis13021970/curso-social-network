<?php

namespace AppBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PrivateMessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['empty_data'];
        $builder
            ->add('receiver',EntityType::class,array(
                 'class' => 'BackendBundle:User',
                'query_builder' => function($er) use($user){
                      return $er->getFollowingUsers($user);
                },
                'choice_label' => function($user){
                     return $user->getName(). " " . $user->getUsername(). "-" . $user->getNick();
                 },
                'label' => 'para:',
                        'attr' => array('class' => 'form-control')
            ))
            ->add('message',TextareaType::class,array(
                'label' => 'Mensaje',
                'required' => 'required',
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('image',FileType::class,array(
                'label' => 'Imagen',
                'required' => null,
                'data_class' => null,
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('file',FileType::class,array(
                'label' => 'Archivo',
                'required' => null,
                'data_class' => null,
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('Enviar',SubmitType::class,array(
                 'attr' => array(
                     'class' => 'btn btn-success'
                 )
            ));


    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BackendBundle\Entity\PrivateMessage'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'backendbundle_user';
    }


}
