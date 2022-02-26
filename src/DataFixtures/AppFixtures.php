<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Role;
use App\Entity\Topic;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
	private $passwordEncoder;
	
	public function __construct(UserPasswordHasherInterface $passwordEncoder)
	{
		$this->passwordEncoder = $passwordEncoder;
	}
	
	public function load(ObjectManager $manager): void
	{
		$roles = [
			'Super Administrator' => 'ROLE_SUPER_ADMIN',
			'Administrator' => 'ROLE_ADMIN',
			'Moderator' => 'ROLE_MOD',
			'User' => 'ROLE_USER'
		];
		
		$rolesTab = [];
		
		foreach ($roles as $name => $slug) {
			$role = new Role();
			$role->setName($name);
			$role->setSlug($slug);
			
			$manager->persist($role);
			
			$rolesTab[] = $role;
		}
	
		$users = [];

		$userList = [
			'superadmin' => 'superadmin@local.host',
			'admin' => 'admin@local.host',
			'mod' => 'mod@local.host',
			'user' => 'user@local.host'
		];
		
		$i = 0;
		
		foreach ($userList as $username => $email) {
			$user = new User();
			$user->setUsername($username);
			$user->setPassword($this->passwordEncoder->hashPassword($user, 'password'));
			$user->setEmail($email);
			$user->setEmailConfirmed(true);
			$user->setRole($rolesTab[$i]);
			
			$manager->persist($user);
			
			$i++;

			$users[] = $user;
		}
		
		$parentCategories = [];
		
		$faker = Factory::create();
		
		for ($i=0; $i < 5; $i++) { 
			$category = new Category();
			$category->setName($faker->sentence(3));
			$category->setDescription($faker->sentence(rand(6,12)));
			
			$manager->persist($category);
			
			$parentCategories[] = $category;
		}
		
		foreach($parentCategories as $parentCategory) {
			for ($i=0; $i < rand(3,4); $i++) { 
				$category = new Category();
				$category->setName($faker->sentence(3));
				$category->setDescription($faker->sentence(rand(6,10)));
				$category->setParent($parentCategory);
				
				$manager->persist($category);
				
				for ($a=0; $a < rand(30,60); $a++) { 
					$topic = new Topic();
					$topic->setCategory($category);
					$topic->setTitle($faker->sentence());
					$topic->setMessage($faker->sentence(24));
					$topic->setAuthor($users[rand(0,3)]);
					
					$manager->persist($topic);
					
					for ($u=0; $u < rand(4,12); $u++) { 
						$post = new Post();
						$post->setMessage($faker->sentence(24));
						$post->setTopic($topic);
						$post->setAuthor($users[rand(0,3)]);

						$manager->persist($post);
					}
				}
			}
		}
		
		$manager->flush();
	}
}
