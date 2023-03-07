<?php

namespace App\Controller;
use App\Entity\Promotion;
use App\Entity\Offres;
use App\Form\OffresType;
use App\Repository\OffresRepository;
use App\Repository\CategorieRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\MyEntityRepository;
use App\Repository\PromotionRepository;
use ChartJs\Builder\ChartBuilderInterface;
use ChartJs\ChartJS;

use Knp\Component\Pager\PaginatorInterface;
 


#[Route('/offres')]
class OffresController extends AbstractController
{
    ///////////////////////////MOBILe/////////////////////////////////////
    

    #[Route('/mobile', name: 'app_offres_index_mobile', methods: ['GET','POST'])]
    public function indexmobile(OffresRepository $offresRepository,SerializerInterface $si)
    { $result=$offresRepository->findAll();
        // dd($result)
        $json =$si->serialize($result, 'json');
        return new Response($json);
    }

//////////////////tri et pagination/////////////////////////////

    #[Route('/', name: 'app_offres_index', methods: ['GET'])]
    
        public function indexx(OffresRepository $offresRepository, Request $request, PaginatorInterface $paginator): Response
        {
            
            $offre = $offresRepository->findAll();
            $offre = $paginator->paginate(
                $offre, /* query NOT result */
                $request->query->getInt('page', 1), /*page number*/
                3/*limit per page*/
            );

    return $this->render('back/tableo.html.twig', [
        'offre' => $offre,
    ]);
        }

    #[Route('/tri', name: 'app_offres_trier', methods: ['GET'])]
    public function afftri(OffresRepository $offresRepository, Request $request, PaginatorInterface $paginator): Response
    {
       
        $offres = $offresRepository->tri();
        $offres = $paginator->paginate(
            $offres, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            3/*limit per page*/
        );

        // Passer les résultats à la vue Twig pour les afficher
        return $this->render('back/tableo.html.twig', [
            'offre' => $offres,
        ]);
    }


/////////////////statistique/////////

        
   
    #[Route('/s', name: 'app_offres_stat', methods: ['GET'])]
  
        
    public function index(OffresRepository $offresRepository): Response
    {
        $offres = $offresRepository->getOffresByAbonnements();
        $ofdate=$offresRepository->getAbonnementsByDate();
        
       
        $labels = [];
        $data = [];
        foreach ($offres as $offre) {
            
            $labels[] = $offre['id'];
        
            $data[] = $offre['nbAbonnements'];
        }
        $label = [];
        $dat = [];
        foreach ($ofdate as $row) {
            if ($row['date']!== null) {
                $label[] = $row['date']->format('d/m/Y');
            }
            $dat[] = $row['nb'];
        }
        
        
        return $this->render('back/offre_abonnements.html.twig', [
            'offre' => $offres,
            'chart_labels' => json_encode($labels),
            'chart_data' => json_encode($data),
            'la'=> json_encode($label),
            'da' => json_encode($dat)
            

        ]);
    }
    //////////////////mobile///////////////////////
    #[Route('/newjson', name: 'app_offres_newjson', methods: ['GET', 'POST'])]
    public function newj(Request $req, OffresRepository $offresRepository,ManagerRegistry $mr,SerializerInterface $sr): Response
    { 
        $em=$mr->getManager();
        $offre = new Offres();
        $offre->setPrix($req->get('prix'));
        $offre->setDescription($req->get('description'));
        $offre->setDuree($req->get('durree'));
        
        $offre->setPromo($req->get('Promo'));
        $offre-> setCateg($req->get('categ'));
        $em->persist($offre);
        $em->flush();

        $json = $sr->serialize($offre, 'json');
        return new Response($json);
        
    }
        
    ////////////////////////////////
        
       

    #[Route('/new', name: 'app_offres_new', methods: ['GET', 'POST'])]
    public function new(Request $request, OffresRepository $offresRepository): Response
    {
        $offre = new Offres();
        $form = $this->createForm(OffresType::class, $offre);
        $form->handleRequest($request);
      
        if($offre->getPromo()!= null){
            $offre->setPrix($offre->getPrix()-(($offre->getPrix()*$offre->getPromo()->getPourcentage())/100));}
           

        if ($form->isSubmitted() && $form->isValid()) {
            $offresRepository->save($offre, true);

            return $this->redirectToRoute('app_offres_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/form.html.twig', [
            'offres' => $offre,
            'form' => $form,
        ]);
    }

   #[Route('/show/{id}', name: 'app_offres_show', methods: ['GET'])]


   public function show(Offres $offres): Response
    {
      return $this->render('back/showo.html.twig', [
            'offre' => $offres,
            
        ]);
    }


#[Route('/{id}/edit', name: 'app_offres_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Offres $offre, OffresRepository $offresRepository): Response
{
    $pr = 0;
    if ($offre->getPromo() != null) {
        $pr = $offre->getPromo()->getPourcentage();
    }

    $form = $this->createForm(OffresType::class, $offre);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        if ($offre->getPromo() && $offre->getPromo()->getDateFin() <= new \DateTime()) {
            // La promotion est expirée, supprimer la référence à la promotion dans l'offre
            $offre->setPromo(null);
        } else {
            if ($offre->getPromo() == null) {
                $offre->setPrix(($offre->getPrix() * 100) / (100 - $pr));
            } else {
                $offre->setPrix($offre->getPrix() - (($offre->getPrix() * $offre->getPromo()->getPourcentage()) / 100));
            }
        }

        $offresRepository->save($offre, true);
        return $this->redirectToRoute('app_offres_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('back/form.html.twig', [
        'offres' => $offre,
        'form' => $form,
    ]);
}




    #[Route('/delte/{id}', name: 'app_offres_delete', methods: ['GET','POST'])]
 public function remove($id,ManagerRegistry $mr,OffresRepository $repo): Response
    {
        
        $st=$repo->find($id);
        if($st==Null)
       {return $this->redirectToRoute('app_offres_index');
        
      }
      else 
       
       {
        $em=$mr->getManager();
        $em->remove($st);
        $em->flush();
        return $this->redirectToRoute('app_offres_index');
    }



          }


          #[Route('/categorie/{id}/offres', name: 'app_categorie_offres', methods: ['GET'])]
          public function afficherOffresParCategorie(int $id, CategorieRepository $categoriesRepository): Response
          {
              $categorie = $categoriesRepository->find($id);
              if (!$categorie) {
                  throw $this->createNotFoundException('La catégorie demandée n\'existe pas.');
              }
          
              $offres = $categorie->getOffres();
          
              return $this->render('front/services.html.twig', [
                  'categorie' => $categorie,
                  'offres' => $offres,
              ]);
          }




          ///////////////////mobileeee///////////////
          #[Route('/{id}/mobile', name: 'app_edit_offres_mob', methods: ['GET'])]
          public function editoffmob(Request $request, $id, Offres $offre,SerializerInterface $sr ,OffresRepository $or): Response
          {
            $offre->setduree($request->get('duree')); 
            $or->save($offre, true);
            $json = $sr->serialize($offre, 'json');
            return new Response($json);


          }
          
}