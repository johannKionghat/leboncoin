<?php
namespace App\Controller\annonce;

use App\Entity\Listings;
use App\Form\AnnonceType;
use App\Repository\ListingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class CrudController extends AbstractController
{
    #[Route('annonce/show/{id}',name:'showAnnonce', requirements: ['id' => Requirement::DIGITS])]
    public function showAnnonce(
        $id,
        ListingsRepository $listingsRepository,
    ):Response{
        $ads=$listingsRepository->find($id);
        return $this->render('crud/annonce/showAnnonce.html.twig',[
            'ads'=>$ads,
        ]);
    }
    #[Route('/annonce/add',name: 'addAnnonce')]
    public function addAnnonce(
        Listings $listings,
        Request $request,
        EntityManagerInterface $em,
        ListingsRepository $listingsRepository,
    ):Response
    {
        $form= $this->createForm(AnnonceType::class, $listings);
        $form->handleRequest($request);
        
        if($form -> isSubmitted() && $form -> isValid() ){
            $em->persist($listings);
            $em->flush();
            /** @var UploadedFile $file */
            $file=$form->get('thumbnailFile')->getData();
            $filename='image'.$listings->getId().'.'.$file->getClientOriginalExtension();
            $file->move($this->getParameter('kernel.project_dir').'/public/annonces/images',$filename);
            $listings->setPhotoUrl($filename);
            $listings->setCreatedAt(new \DateTime());
            $em->persist($listings);
            $em->flush();
            $this->addFlash('succes', 'Annonce ajouter avec succes !');
            return $this->redirectToRoute('app_homeUser');
        }
        $annonces = $listingsRepository->findAll();
        return $this->render('crud/annonce/addAnnonce.html.twig',[
            'formAddannonce'=>$form,
            'annonces'=>$annonces,
        ]);
    }
    #[Route('annonce/edit/{id}',name:'editAnnonce', requirements: ['id' => Requirement::DIGITS])]

    public function edit($id, Request $request, ListingsRepository $listingsRepository, EntityManagerInterface $em):Response
    {
        $listings=$listingsRepository->find($id);
        $form = $this->createForm(AnnonceType::class, $listings);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            
            if ( $form->get('thumbnailFile')->getData() !== null){
                /** @var UploadedFile $file */
                $file=$form->get('thumbnailFile')->getData();
                $filename='image'.$listings->getId().'.'.$file->getClientOriginalExtension();
                $file->move($this->getParameter('kernel.project_dir').'/public/annonces/images',$filename);
                $listings->setPhotoUrl($filename);
            }
            $listings->setCreatedAt(new \DateTime());
            $em->flush();
            $this->addFlash('success', 'L\'annonce a été modifié avec succès');
            return $this->redirectToRoute('app_homeUser');
        }
        return $this->render('crud/annonce/editAnnonce.html.twig',[
            'formEditannonce'=>$form,
        ]);
    }
    #[Route('annonce/delete/{id}',name:'deleteAnnonce', requirements: ['id' => Requirement::DIGITS])]

    public function delete($id,ListingsRepository $listingsRepository, EntityManagerInterface $em):Response
    {
        $listings=$listingsRepository->find($id);
        $em->remove($listings);
        $em->flush();
        $this->addFlash('success', 'Annonce supprimer avec succès');
        return $this->redirectToRoute('app_homeUser');
    }

}