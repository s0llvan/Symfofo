<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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

		$users = [
			'superadmin' => 'superadmin@local.host',
			'admin' => 'admin@local.host',
			'mod' => 'mod@local.host',
			'user' => 'user@local.host'
		];

		$i = 0;

		foreach ($users as $username => $email) {
			$user = new User();
			$user->setUsername($username);
			$user->setPassword($this->passwordEncoder->hashPassword($user, 'password'));
			$user->setEmail($email);
			$user->setEmailConfirmed(true);
			$user->setRole($rolesTab[$i]);
			
			$manager->persist($user);

			$i++;
		}
		
		$manager->flush();
	}
}
