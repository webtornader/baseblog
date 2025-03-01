<?php

namespace App\Form;

use App\Entity\Blog;
use App\Entity\BlogCategory;
use App\Entity\User;
use App\Form\DataTransformer\TagTransformer;
use App\Repository\BlogCategoryRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogType extends AbstractType
{
    public function __construct(
        private readonly TagTransformer $transformer,
        private readonly Security $security,
    )
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'help' => 'Заполните заголовок текста.',
                'attr' => ['class' => 'myclass'],
            ])
            ->add('description', TextareaType::class, ['required' => true])
            ->add('text', TextareaType::class, ['required' => true])
            ->add('blogCategory', EntityType::class, [
                // looks for choices from this entity
                'class' => BlogCategory::class,
                'query_builder' => function (BlogCategoryRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.title', 'ASC');
                },
                // uses the User.username property as the visible option string
                'choice_label' => 'title',
                'placeholder' => '-----------',
                'required' => false,
                'empty_data' => null,

                // used to render a select box, check boxes or radios
                // 'multiple' => true,
                // 'expanded' => true,
            ])
            ->add('tags', TextType::class, [
                'label' => 'Теги',
                'required' => false,
            ]);
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder->add('User', EntityType::class, [
                // looks for choices from this entity
                'class' => User::class,
                'query_builder' => function (UserRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.email', 'ASC');
                },
                // uses the User.username property as the visible option string
                'choice_label' => 'email',
                'placeholder' => '-----------',
                'required' => true,
                'empty_data' => null,

                // used to render a select box, check boxes or radios
                // 'multiple' => true,
                // 'expanded' => true,
            ]);
    }

        $builder->get('tags')->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
        ]);
    }
}
