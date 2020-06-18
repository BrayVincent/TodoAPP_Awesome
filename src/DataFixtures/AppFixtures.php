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

    public function __construct(UserPasswordEncoderInterface $encoder){
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        
       //Création nouvel objet Faker
        $faker=Factory::create('fr_FR');

        
        for($i=0;$i<5;$i++){
            //Création nouvel objet Tag
            $tag=new Tag;
            //On nourrit l'objet
            //$tag->setName($faker->randomElement(['pro','loisirs','bizarre','santé','hasard']));
            $tag->setName($faker->colorName())
            ->setColor($faker->hexcolor());;
            //On fait persister les données
            $manager->persist($tag);
        }
        //Envoyer les données en BDD (ici on doit flush pour chaque table car elles ont un lien entre elles)
        $manager->flush();

        for($i=0;$i<mt_rand(15,30);$i++){
            //Création objet Task
            $task= new Task;
            //On récupère tous le enregistrements de la table Tag
            $allTags= $manager->getRepository(Tag::class)->findAll();

            //On nourrit l'objet
            $startDate=$faker->dateTimeBetween('-2 days ', '+2 days');
            $task->setName($faker->sentence(6))
            ->setDescription($faker->paragraph(3))
            ->setCreatedAt(new \Datetime()) //Dates créées en fonction du serveur
            ->setDueAt($faker->dateTimeBetween($startDate, '+5 days'))
            ->setTag($faker->randomElement($allTags))
            ->setStartAt($startDate);

            //On fait persister les données
            $manager->persist($task);

        }

        for($u = 0; $u < 5; $u++){
            //Creation nouvel utilisateur
            $user = new User;

            //Hashage password avec parametre de sécurité de $user
            $hash = $this->encoder->encodePassword($user, "password");
            // On nourrit l'objet User
            // Si premier utilisateur crée on lui donne le rôle admin
            if ($u === 0) {
                $user->setRoles(['ROLE_ADMIN'])->setEmail('admin@admin.fr')
                    ->setIsValid(true);

            } else {
                $user->setEmail($faker->safeEmail())->setIsValid(false);
            }

            $user->setPassword($hash);

            // On fait persister les données
            $manager->persist($user);


        }
        //Envoyer les données en BDD
        $manager->flush();
    }
}
