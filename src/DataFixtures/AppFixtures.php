<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Tag;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {


        // Création d'un nouvel objet Faker
        $faker = Factory::create('fr_FR');

        //Création de nos 5 catégories
        for ($c = 0; $c < 5; $c++) {
            //Création d'un nouvel objet Tag
            $tag = new Tag;

            // On ajout un nom à notre catégorie
            // $tag->setName($faker->randomElement(['pro','loisirs','santé','bizarre','hasard']));
            $tag->setName($faker->colorName());

            // On fait persister les données
            $manager->persist($tag);
        }

        // On push les catégories en BDD
        $manager->flush();

        //récupération des catégories crées
        $allTags = $manager->getRepository(Tag::class)->findAll();

        // Création entre 15 et 30 tâches aléatoirement
        for ($t = 0; $t < mt_rand(15, 30); $t++) {

            // Création d'un nouvel objet Task
            $task = new Task;

            // On nourrit l'objet Task
            $startDate = $faker->dateTimeBetween('-2 days', '+ 2 days');
            $task->setName($faker->sentence(6))
                ->setDescription($faker->paragraph(3))
                ->setCreatedAt(new \DateTime()) // Attention les dates sont crées en fonction du réglage serveur
                ->setDueAt($faker->dateTimeBetween($startDate, '+ 10 days')) // de même ici
                ->setTag($faker->randomElement($allTags))
                ->setStartAt($startDate);

            // On fait persister les données
            $manager->persist($task);
        }

        // Création de 5 utilisateurs
        for ($u = 0; $u < 5; $u++) {

            // Création d'un nouvel objet User
            $user = new User;

            // Hashage de notre mot de passe avec les paramètres de sécurité de notre $user
            // dans /config/packages/security.yaml
            $hash = $this->encoder->encodePassword($user, "password");

            // Si premier utilisateur crée on lui donne le rôle admin
            if ($u === 0) {
                $user->setRoles(["ROLE_ADMIN"])
                    ->setEmail('admin@admin.fr');
            } else {
                // On nourrit l'objet User
                $user->setEmail($faker->safeEmail());
            }

            $user->setPassword($hash)
                ->setIsValid(false);


            // On fait persister les données
            $manager->persist($user);
        }

        // On push le tout en BDD
        $manager->flush();
    }
}
