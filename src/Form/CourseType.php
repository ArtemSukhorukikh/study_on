<?php

namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code',TextType::class, [
                'label'=>'Код курса',
                'constraints' => [
                    new NotBlank(message: 'Поле не может быть пустым.'),
                    new Length(max: 255, maxMessage: 'Код курса не должно превышать 255 символов.')
                ],
            ])
            ->add('name', TextType::class, [
                'label'=>'Название курса',
                'constraints' => [
                    new NotBlank(message: 'Поле не может быть пустым.'),
                    new Length(max: 255, maxMessage: 'Название курса не должно превышать 255 символов.')
                ],
            ])
            ->add('description', TextareaType::class,[
                'label' => 'Описание курса.',
                'constraints' => [
                    new NotBlank(message: 'Поле не может быть пустым.'),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
