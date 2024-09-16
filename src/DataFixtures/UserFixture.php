<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }
    public function load(ObjectManager $manager): void
    {
        $faker=Factory::create();
        for($i=0; $i<100;$i++){
            $user=new User();
            $user->setEmail($faker->email);
            $user->setNom($faker->firstNameMale);
            $user->setPrenom($faker->lastName);
            $user->setUserName($faker->firstNameMale);
            $user->setNumTel(92703351);
            $user->setImage($faker->imageUrl($width = 640, $height = 480));
            $password = $this->hasher->hashPassword($user, 'Dhia1234');
            $user->setPassword($password);
            $manager->persist($user);
        }
        // $product = new Product();
        // $manager->persist($product);
        $manager->flush();
    }
}
