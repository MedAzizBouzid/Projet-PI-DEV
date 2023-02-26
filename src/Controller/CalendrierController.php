<?php

namespace App\Controller;

use App\Entity\Calendrier;
use App\Entity\Salle;
use App\Entity\User;
use App\Form\CalendrierType;
use App\Repository\SalleRepository;
;

use App\Repository\CalendrierRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/calendrier')]
class CalendrierController extends AbstractController
{
    #[Route('/', name: 'app_calendrier_index', methods: ['GET'])]
    public function index(Request $request,CalendrierRepository $calendrierRepository): Response
    {

        //accéder au repo calendrier
        $events = $calendrierRepository->findAll();
        // créer un tableau Json a fin de stocker les données 
        $coach=new User();
        $rdvs = [];
        foreach($events as $event){
            $coach=$event->getCoach();
            // dd($coach);
            $rdvs[] = [
                'id' => $event->getId(),    
                'start' => $event->getStart()->format('Y-m-d H:i:s'),
                'end' => $event->getEnd()->format('Y-m-d H:i:s'),
                'title' => $event->getActivite()."\n".$event->getCoachName(),
                'description' => $event->getDescription(),
                // 'activite' => $event->getActivite(),

                'backgroundColor' => $event->getBackgroundColor(),
                'borderColor' => $event->getBorderColor(),
                'textColor' => $event->getTextColor(),

             ];

        }
        $data = json_encode($rdvs);
        // dd($data);

 
 

        $calendrier = new Calendrier();
        $form = $this->createForm(CalendrierType::class, $calendrier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $calendrierRepository->save($calendrier, true);

            return $this->redirectToRoute('app_calendrier_index', [], Response::HTTP_SEE_OTHER);
        }
        // stoker dans la variable data le contenue du tab json 

        // return $this->render('back/FullCalender.html.twig', [
        //     'calendriers' => $calendrierRepository->findAll(),
        //     // 'form' => $form, 
        //     'data' => $data, 
        //     compact('data')



            return $this->render('back/FullCalender.html.twig', compact('data','form'));
   

    }
// _____________________________________________________________________________________________________________________________________________
 //     return $this->render('back/FullCalender.html.twig', 
        
    //     compact('data')
        
    // );
    #[Route('/{id}/new', name: 'app_calendrier_new', methods: ['GET', 'POST'])]
     public function new($id,Request $request, CalendrierRepository $calendrierRepository,SalleRepository $SalleRe,UserRepository $userRepository): Response
    {

        $salle  = new Salle();
        $salle=$SalleRe->find($id);

        $calendrier = new Calendrier();
        $form = $this->createForm(CalendrierType::class, $calendrier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // dd($request->get('coach'));
        $coach=$userRepository->find($request->get('coach'));
        $calendrier->setActivite($request->get('Salle'));
        $calendrier->setCoach($coach);
        $calendrier->setSalla($salle);
            $calendrierRepository->save($calendrier, true);

            return $this->redirectToRoute('app_calendrier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('calendrier/new.html.twig', [
            'calendrier' => $calendrier,
            'salle' => $salle,
            'form' => $form,
            'coachs' => $userRepository->findAllUser('["ROLE_COACH"]'),
        ]);
    }

    #[Route('/{id}', name: 'app_calendrier_show', methods: ['GET'])]
    public function show(Calendrier $calendrier): Response
    {
        return $this->render('calendrier/show.html.twig', [
            'calendrier' => $calendrier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_calendrier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Calendrier $calendrier, CalendrierRepository $calendrierRepository): Response
    {
        $form = $this->createForm(CalendrierType::class, $calendrier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $calendrierRepository->save($calendrier, true);

            return $this->redirectToRoute('app_calendrier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('calendrier/edit.html.twig', [
            'calendrier' => $calendrier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_calendrier_delete', methods: ['POST'])]
    public function delete(Request $request, Calendrier $calendrier, CalendrierRepository $calendrierRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$calendrier->getId(), $request->request->get('_token'))) {
            $calendrierRepository->remove($calendrier, true);
        }

        return $this->redirectToRoute('app_calendrier_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/find', name: 'find_calendar', methods: ['GET', 'POST'])]
    public function find_event(CalendrierRepository $calendar)
    {
        $events = $calendar->findAll();

        $rdvs = [];

        foreach($events as $event){
            $rdvs[] = [
                'id' => $event->getId(),
                'start' => $event->getStart()->format('Y-m-d H:i:s'),
                'end' => $event->getEnd()->format('Y-m-d H:i:s'),
                // 'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'backgroundColor' => $event->getBackgroundColor(),
                'borderColor' => $event->getBorderColor(),
                'textColor' => $event->getTextColor(),
             ];
        }

        $data = json_encode($rdvs);

        return $this->render('main/index.html.twig', compact('data'));
    }

// _____________________ la methode magique api conservation des données aprés un mouvement d'event_____
                    //  ___________________Keeeeeeep it secrettt___________________
    #[Route('/api/{id}/edit', name: 'api_event_api', methods: ['PUT'])]
   public function MiseAjourEvent(Calendrier $calendar,ManagerRegistry $mg, CalendrierRepository $repo,Request $request){

  // On récupère les données
        $donnees = json_decode($request->getContent());
   
        if(
            isset($donnees->title) && !empty($donnees->title) &&
            isset($donnees->start) && !empty($donnees->start) &&
            isset($donnees->end) && !empty($donnees->end) &&

            isset($donnees->description) && !empty($donnees->description) &&
            isset($donnees->background_color) && !empty($donnees->background_color) &&
            isset($donnees->border_color) && !empty($donnees->border_color) &&
            isset($donnees->text_color) && !empty($donnees->text_color)
        ) {
            // Les données sont complètes
            // On initialise un code pour l'envoyer dans la console 
            $code = 200;

            // On vérifie si l'id existe  ( si l'objet existe)
            if(!$calendar){
                // On instancie un plannig
                $calendar = new Calendrier;

                // On change le code
                $code = 201;
            }
  
            // Si oui (l'objet existe on injecte les données vers une url bien precise)
            // $calendar->setTitle($donnees->title);
            $calendar->setStart(new DateTime($donnees->start));
            $calendar->setStart(new DateTime($donnees->end));
            $calendar->setDescription($donnees->description);
            $calendar->setBackgroundColor($donnees->background_Color);
            $calendar->setBorderColor($donnees->border_Color);
            $calendar->setTextColor($donnees->text_Color);
              
dd($calendar);
            // $em = $mg->getManager(); 
            // $em->persist($calendar);
            // $em->flush();

            // On retourne le code
            $repo->save($calendar, true);

            return new Response('Bien reçu', $code);
        }else{
            // Les données sont incomplètes et envoyer 404 NOT FOUND
            return new Response('Données incomplètes', 404);
        }
        return $this->render('back/FullCalender.html.twig', [
            'controller_name' => 'ApiController',
        ]);

   }

}