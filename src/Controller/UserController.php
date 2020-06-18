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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

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

    const TOKENMAXMINUTES = '15';

    /**
     * constructeur de UserController
     *
     * @param UserRepository $repository
     * @param EntityManagerInterface $manager
     * @param TranslatorInterface $translator
     */
    public function __construct(UserRepository $repository,
                                EntityManagerInterface $manager,
                                UserPasswordEncoderInterface $encoder,
                                TranslatorInterface $translator)
    {
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
     * @Route("/users/update/{id}", name="update_user", requirements={"id"="\d+"})
     * @param Request $request
     * @return Response
     */
    public function user(
        User $user = null,
        Request $request,
        TokenGeneratorInterface $tokenGenerator,
        \Swift_Mailer $mailer
    ): Response {
        if (!$user) {
            $user = new User();
            $flag = true;
        } else {
            $flag = false;
        }

        //création formulaire
        $form = $this->createForm(UserType::class, $user, []);

        //récupération des données de formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            if (
                $this->isValidRecaptcha($request->get('g-recaptcha-response'))
            ) {
                // Hashage du mot de passe
                $hash = $this->encoder->encodePassword(
                    $user,
                    $form['password']->getData()
                );

                // recherche en base si le user (email) existe
                $user_bdd = $this->repository->findOneBy([
                    'email' => $form['email']->getData(),
                ]);

                // Si le user existe en base et qu'on essaye de le créer
                if ($user_bdd !== null and $flag) {
                    $this->addFlash(
                        'danger',
                        $this->translator->trans('flash.user.doublon')
                    );
                    unset($user_bdd);

                    return $this->redirectToRoute('user_create');
                } else {
                    // si user pas en base et new user
                    if (!$user_bdd and $flag) {
                        //ajouter la date de création du token (tokenAt)
                        $date = new \DateTime();
                        $user->setTokenAt($date);

                        //génération du token
                        $token = $tokenGenerator->generateToken();
                        $user->setToken($token);

                        //is Valid false
                        $user->setIsValid(false);

                        $user
                            ->setEmail($form['email']->getData())
                            ->setPassword($hash);

                        //on fait persister notre user
                        $this->manager->persist($user);
                        //on flush le tout en BDD
                        $this->manager->flush();

                        // modification de la route
                        $url = $this->generateUrl(
                            'check_token',
                            ['id' => $user->getId(), 'token' => $token],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );

                        $message = (new \Swift_Message(
                            "Confirmation d'inscription"
                        ))
                            ->setFrom('contact@mail.com')
                            ->setTo($form['email']->getData())
                            ->setBody(
                                'click here for confirm account: ' . $url,
                                'text/html'
                            );

                        $mailer->send($message);

                        $this->addFlash('notice', 'mail envoyé');

                        // TODO route
                        return $this->redirectToRoute('app_login');
                    }

                    //modif user
                    $user
                        ->setEmail($form['email']->getData())
                        ->setPassword($hash);

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
                }
                return $this->redirectToRoute('users_listing');
            } else {
                //are you a bot ?
                $this->addFlash(
                    'danger',
                    $this->translator->trans('flash.user.errReCaptcha')
                );
            }
        }

        return $this->render('user/create.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


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



    /**
    * @Route("users/check_token/{id}/{token}", name="check_token", requirements={"id"="\d+"})
    *
    * @param [type] $token
    * @return void
    */
    public function checkToken(User $user, $token): Response
    {
        //comparer token et intervalle temps
        $current_time = new \Datetime();
        $expire_time = date_add(
        $current_time,
        date_interval_create_from_date_string(
        self::TOKENMAXMINUTES . ' minutes'
        ));
        if (
        $user->getToken() === $token and
        $user->getTokenAt() <= $expire_time
        ) {
        $user
        ->setToken(null)
        ->setTokenAt(null)
        ->setIsValid(true);
        //on fait persister notre user
        $this->manager->persist($user);
        //on flush le tout en BDD
        $this->manager->flush();
        return $this->redirectToRoute('app_login');
        }
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
