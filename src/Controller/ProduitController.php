<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\CategorieRepository;
use App\Repository\LignePanierRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('produit')]
class ProduitController extends AbstractController
{
    #[Route('/show/back', name: 'app_produit_index_back', methods: ['GET'])]
    public function AffichageBack(ProduitRepository $produitRepository): Response
    {
        return $this->render('back/produit/ProduitsB.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }
    /******************************************MobileAddProd************************************************ */
    #[Route('/showMobile', name: 'app_produit_index_mobile', methods: ['GET', 'POST'])]
    public function AffichageMobile(ProduitRepository $produitRepository, SerializerInterface $si)
    {
        $result = $produitRepository->findAll();
        $json = $si->serialize($result, 'json', ['groups' => "produits"]);
        return new Response($json);
    }
    /*******************************************AjoutMobile*********************** */
    #[Route('/ajoutMobile', name: 'app_produit_ajout_mobile', methods: ['GET', 'POST'])]
    public function AjoutMobile(Request $req,ProduitRepository $produitRepository, SerializerInterface $si)
    {   $p=new Produit();
        $p->setNom($req->get('nom'));
        $p->setImage($req->get('image'));
        $p->setPrix($req->get('prix'));
        $p->setStock($req->get('stock'));
        
        $p = $produitRepository->save($p,true);
        $json = $si->serialize($p, 'json', ['groups' => "produits"]);
        return new Response($json);
    }
    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProduitRepository $produitRepository, SluggerInterface $slugger): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $photo = $form->get('photo')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($photo) {
                $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photo->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $photo->move(
                        $this->getParameter('produit_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'photo$photoname' property to store the PDF file name
                // instead of its contents
                $produit->setImage($newFilename);
            }

            $produitRepository->save($produit, true);

            return $this->redirectToRoute('app_produit_index_back', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('back/produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produitRepository->save($produit, true);

            return $this->redirectToRoute('app_produit_index_back', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $produit->getId(), $request->request->get('_token'))) {
            $produitRepository->remove($produit, true);
        }

        return $this->redirectToRoute('app_produit_index_back', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/show/front', name: 'app_produit_index_front', methods: ['GET'])]
    public function AffichageFrontPaginated(Request $request, ProduitRepository $produitRepository, LignePanierRepository $LPR, CategorieRepository $categorieRepository): Response
    {
        $nbr[] = $LPR->countAll();
        //on va chercher le num page dans l'url
        $page = $request->query->getInt('page', 1);
        //on va cherche la liste des produits paginated 
        $produit = $produitRepository->findProductPaginated($page, 3);

        //dd($produit);
        return $this->render('front/produitShow.html.twig', [
            'produits' => $produit,

        ]);
    }

    #[Route('/editJson/{id}', name: 'app_produit_user_edit', methods: ['GET'])]
    public function updatejson(Request $request, Produit $produit, ProduitRepository $produitRepository, SerializerInterface $sr, CategorieRepository $categorieRepository): Response
    {
        $produit->setNom($request->get('nom'));
        $produitRepository->save($produit, true);
        $json = $sr->serialize($produit, 'json', ['groups' => "produits"]);
        return new Response($json);
    }
    #[Route('/detailJson/{id}', name: 'app_produit_user_detail_mobile', methods: ['GET'])]
    public function DetailJson($id,Request $request, ProduitRepository $produitRepository, SerializerInterface $sr): Response
    {
        $id = $request->get("id");
        $produit = $produitRepository->find($id);
        $produitRepository->save($produit, true);
        $json = $sr->serialize($produit, 'json', ['groups' => "produits"]);
        return new Response($json);
    }
}
