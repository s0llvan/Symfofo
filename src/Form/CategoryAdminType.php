<?php

namespace App\Form;

use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryAdminType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
		->add('name')
		->add('description');
		
		if($options['parent']) {
			$builder->add('parent', EntityType::class, [
				'class' => Category::class,
				'choice_label' => 'name',
				'query_builder' => function (EntityRepository $er) {
					return $er->createQueryBuilder('c')
					->where('c.parent is NULL');
				}
			]);
		}
		
		$builder
		->add('save', SubmitType::class, ['attr' => [
			'class' => 'ui button'
			]
		]);
	}
	
	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Category::class,
			'parent' => false
		]);
	}
}
