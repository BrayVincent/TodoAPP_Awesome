<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PDFController extends AbstractController
{
    /**
    * @Route("/pdf/{entity}", name="pdf_generate" )
    */
    public function pdf(string $entity, ManagerRegistry $manager)
    {
        // Déclaration des variables

        // Pour aller chercher les données, on a besoin du Repository de l'entité
        // On construit le nom du Repository à partir de l'entité récupérée dans l'URL
        $reponame = 'App\Repository\\' . ucfirst($entity) . 'Repository';
        // On récupère l'objet correspondant au Repository (avec le manager en paramètre)
        $repo = new $reponame($manager);
        // On récupère les données dans une variable
        $datas = $repo->findAll();
        // On génère le contenu à partir du fichier twig
        $html = $this->renderView('pdf/' . $entity . '.html.twig', ['datas' => $datas]);
        // On crée de l'objet dompdf
        $dompdf = new Dompdf();
        // On le charge dans le pdf
        $dompdf->loadHtml($html);
        // On souhaite un format A4 en mode portrait (vertical)
        // pour un mode paysage saisir : 'landscape'
        $dompdf->setPaper('A4', 'portrait');
        // Obtenir le rendu
        $dompdf->render();
        // On souhaite que le pdf s'ouvre dans le navigateur (sans téléchargement immédiat)
        // pour un téléchargement direct (changer false par true)
        // "tasks_listing.pdf" sera le nom par défaut lors de l'enregistrement du pdf
        $dompdf->stream($entity . "_listing.pdf", [
        "Attachment" => false]);
    }

}
