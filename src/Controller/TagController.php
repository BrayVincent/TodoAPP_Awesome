<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Task;
use App\Form\TagType;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class TagController extends AbstractController
{
    /**
     * @var TagRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Constructeur du TagController pour injection de dépendances 
     * 
     * @param TagRepository $repository
     * @param EntityManagerInterface $manager
     * @param TranslatorInterface $translator
     */
    public function __construct(TagRepository $repository, EntityManagerInterface $manager, TranslatorInterface $translator)
    {
        $this->repository = $repository;
        $this->manager = $manager;
        $this->manager = $translator;
    }

    /**
     * @Route("/tags/listing", name="tags_listing")
     */
    public function tagslisting()
    {
        //$repository = $this->getDoctrine()->getRepository(Tag::class);
        $tags = $this->repository->findAll();


        //dd($tags);
        return $this->render('tag/index.html.twig', [
            'tags' => $tags,
        ]);
    }

    /**
     * @Route("/tags/create", name="tag_create")
     * @Route("/tags/update/{id}", name="tag_update", requirements={"id"="\d+"})
     * 
     * @param Request $request
     * @return Response
     */
    public function tag(Tag $tag = null, Request $request): Response
    {
        //Création d'un drapeau et de l'objet tag 
        if (!$tag) {
            $tag = new Tag();
            $flag = true;
        } else {
            $flag = false;
        }
        //Créer le formulaire
        $form = $this->createForm(TagType::class, $tag, array());

        //Recupérer notre formulaire
        $form->handleRequest($request);

        //Teste pour valider le formulaire
        if ($form->isSubmitted() and $form->isValid()) {

            //On recupere le nom du tag entré dans le formulaire
            $name = $form['name']->getData();

            //On contrôle si le nom du tag existe déjà
            if ($this->repository->findBy(['name' => $name])) {

                //Si il existe, afficher un message
                $this->addFlash(
                    'danger',
                    $this->translator->trans('tag.alert.alreadyexist')
                );

                //On retourne sur le formulaire de création
                return $this->redirectToRoute('tag_create');

                //Si le nom n'éxiste pas
            } else {

                $tag->setName($name);

                //On le fait persister
                $this->manager->persist($tag);

                //On flush le tout en BDD
                $this->manager->flush();

                if ($flag) {
                    //Message si création
                    $this->addFlash(
                        'success',
                        $this->translator->trans('tag.success.add')
                    );
                } else {
                    //Message si modification
                    $this->addFlash(
                        'success',
                        $this->translator->trans('tag.success.add')
                    );
                }
                return $this->redirectToRoute('tags_listing');
            }
        }
        return $this->render('tag/create.html.twig', ['tag' => $tag, 'form' => $form->createView()]);
    }

    /**
     * @Route ("tags/delete/{id}", name="tag_delete", requirements={"id"="\d+"})
     *
     * @param Tag $tag
     * @return Response
     */
    public function deleteTag(Tag $tag): Response
    {
        //Récupérer tous les objets Task
        $taskRepository = $this->getDoctrine()->getRepository(Task::class);

        //Contrôle si le tag est utilisé
        if ($taskRepository->findBy(['tag' => $tag])) {

            //Si il existe, on affiche un message
            $this->addFlash('danger', $this->translator->trans('tag.alert.busy'));

            //Si le tag n'est pas utilisé
        } else {
            //Supprimer l'objet
            $this->manager->remove($tag);

            //On push dans la BDD
            $this->manager->flush();

            //On affiche un message
            $this->addFlash(
                'success',
                $this->translator->trans('general.success.update')
            );
        }

        //On retourne su la page listing
        return $this->redirectToRoute('tags_listing');
    }
}
