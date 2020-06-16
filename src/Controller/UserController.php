<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * constructeur de UserController
     *
     * @param UserRepository $repository
     * @param EntityManagerInterface $manager
     * @param TranslatorInterface $translator
     */
    public function __construct(
        UserRepository $repository,
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $encoder,
        TranslatorInterface $translator
    ) {
        $this->repository = $repository;
        $this->manager = $manager;
        $this->encoder = $encoder;
        $this->translator = $translator;
    }


    /**
     * @Route("/users/listing", name="users_listing")
     */
    public function userListing()
    {

        //récupérer les infos de l'utilisateur connecté
        $user = $this->getUser();
        // dd($user);

        $users = $this->repository->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users //,
        ]);
    }


    /**
     * @Route("/users/create", name="user_create")
     *  @Route("/users/update/{id}", name="update_user", requirements={"id"="\d+"})
     * @param Request $request
     * @return Response
     */
    public function user(User $user = null, Request $request): Response
    {

        if (!$user) {
            $user = new User();
            $flag = true;
        } else {
            $flag = false;
        }

        //création formulaire
        $form = $this->createForm(UserType::class, $user, array());

        //récupération des données de formulaire
        $form->handleRequest($request);



        if ($form->isSubmitted() and $form->isValid()) {
            if ($this->isValidRecaptcha($request->get('g-recaptcha-response'))) {
                // Hashage du mot de passe
                $hash = $this->encoder->encodePassword($user, $form['password']->getData());
                $user->setEmail($form['email']->getData())
                    ->setPassword($hash)
                    ->setIsValid(false);

                //on fait persister notre user
                $this->manager->persist($user);
                //on flush le tout en BDD
                $this->manager->flush();


                if ($flag) {
                    $this->addFlash(
                        'success',
                        $this->translator->trans('flash.user.ajout')
                    );
                } else {
                    $this->addFlash(
                        'success',
                        $this->translator->trans('flash.user.modif')
                    );
                }

                return $this->redirectToRoute('users_listing');
            } else {
                //are you a bot ?
                $this->addFlash('danger', $this->translator->trans('flash.user.errReCaptcha'));
            }
        }


        return $this->render(
            'user/create.html.twig',
            ['user' => $user, 'form' => $form->createView()]
        );
    }


    /**
     *  @Route("/users/reset/{id}", name="password_reset", requirements={"id"="\d+"})
     */
    // public function resetPassword(User $user): Response
    // {
    //     $user->setPassword('');
    //     $this->manager->flush();
    //     $this->addFlash('warning', $this->translator->trans('flash.user.modif'));
    //     return $this->redirectToRoute('users_listing');
    // }


    /**
     * @Route("users/delete/{id}", name="user_delete", requirements={"id"="\d+"})
     *
     * @param User $id
     * @return Response
     */
    public function deleteUser(User $user): Response
    {
        $this->manager->remove($user);
        $this->manager->flush();
        $this->addFlash('warning', $this->translator->trans('flash.user.suppr'));
        return $this->redirectToRoute('users_listing');
    }


    private function isValidRecaptcha($recaptcha)
    {
        if (empty($recaptcha)) {
            return false; // Si aucun code n'est entré, on ne cherche pas plus loin
        }
        $params = [
            'secret'    => '6Lf_VKMZAAAAADqu-AkqG5MqHZA3c28RtFYV2E6g',
            'response'  => $recaptcha
        ];

        $url = "https://www.google.com/recaptcha/api/siteverify?" . http_build_query($params);
        if (function_exists('curl_version')) {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($curl);
        } else {
            // Si curl n'est pas dispo, un bon vieux file_get_contents
            $response = file_get_contents($url);
        }

        if (empty($response) || is_null($response)) {
            return false;
        }

        $json = json_decode($response);
        return $json->success;
    }
}
