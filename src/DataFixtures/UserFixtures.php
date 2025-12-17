<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Admin user
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setFirstname('Admin');
        $admin->setLastname('Test');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin123')
        );
        $manager->persist($admin);

        // Regular user
        $user = new User();
        $user->setEmail('user@test.com');
        $user->setFirstname('User');
        $user->setLastname('Test');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, 'user123')
        );
        $manager->persist($user);

        // Additional test user (for comments tests)
        $userExample = new User();
        $userExample->setEmail('user@example.com');
        $userExample->setFirstname('Example');
        $userExample->setLastname('User');
        $userExample->setRoles(['ROLE_USER']);
        $userExample->setPassword(
            $this->passwordHasher->hashPassword($userExample, 'user')
        );
        $manager->persist($userExample);

        $manager->flush();
    }
}
