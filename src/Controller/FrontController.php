<?php
namespace App\Controller;

use App\Entity\Calendar;
use App\Entity\Cat;
use App\Entity\Categorie;
use App\Entity\Certificat;
use App\Entity\Comment;
use App\Entity\Commenter;
use App\Entity\Evenement;
use App\Entity\Forum;
use App\Entity\Freelancer;
use App\Entity\Offre;
use App\Entity\Postuler;
use App\Entity\Projet;
use App\Entity\Reclamation;
use App\Entity\Recruteur;
use App\Entity\Test;
use App\Form\CalendarType;
use App\Form\CandidatType;
use App\Form\CategorieType;
use App\Form\CommenterType;
use App\Form\CommentType;
use App\Form\CreateType;
use App\Form\EvenementType;
use App\Form\ForumType;
use App\Form\FreelancerType;
use App\Form\OffreType;
use App\Form\ProjetType;
use App\Form\ReclamationType;
use App\Form\RecruteurType;
use App\Repository\CalendarRepository;
use App\Repository\CategorieRepository;
use App\Repository\CatRepository;
use App\Repository\CertificatRepository;
use App\Repository\CommenterRepository;
use App\Repository\CommentRepository;
use App\Repository\EvenementRepository;
use App\Repository\ForumRepository;
use App\Repository\OffreRepository;
use App\Repository\PostulerRepository;
use App\Repository\ProjetRepository;
use App\Repository\ReclamationRepository;
use App\Repository\RecruteurRepository;
use App\Repository\TestRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Serializer;
use Twilio\Rest\Client as Client;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
class FrontController extends AbstractController
{
    /**
     * @Route("/liste1",name="liste1")
     */
    public function getCompteJSON(NormalizerInterface $Normalizer,TestRepository $repository): Response
    {
        $tests = $repository->findAll();


        $jsonContent = $Normalizer->normalize($tests, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }
    /**
     *
     * @Route("/json_loginjob/{mail}/{mdp}",name="json_loginjob")
     */

    public function signinAction(RecruteurRepository $repository,$mail,$mdp,NormalizerInterface $Normalizer) {


        $em = $this->getDoctrine()->getManager();
        $recruteur = $repository->findOneBy(array('mail'=>$mail,'mdp'=>$mdp));        //ken l9ito f base
        if($recruteur){
            //lazm n9arn password zeda madamo crypté nesta3mlo password_verify


            $formatted = $Normalizer->normalize($recruteur, 'json', ['groups' => 'post:read']);
            return new JsonResponse($formatted);

        }
        else {
            $compte = new  Recruteur();
            $compte->setId(0);
            $compte->setNom('not found');
            $compte->setPrenom('null');
            $compte->setNomsociete('null');
            $compte->setAdresse('null');
            $compte->setMail('null');
            $compte->setNumsociete(0);
            $compte->setMdp('null');
            $compte->setType('null');
            $compte->setPhoto('null');
            $compte->setCompetence('null');
            $jsonContent=$Normalizer->normalize($compte,'json',['groups'=>'post:read']);
            return new Response(json_encode($jsonContent,JSON_UNESCAPED_UNICODE));

        }
    }
    /**
     * @Route("/front/", name="front")
     */
    public function index(CategorieRepository $categorieRepository,Request $request,RecruteurRepository $repository,OffreRepository $offreRepository): Response
    {
        $recruteur = new Recruteur();
        $form=$this->createForm(RecruteurType::class,$recruteur);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recruteurCheck = $repository->findOneBy(['mail' => $recruteur->getMail()]);
            if($recruteur->getMdp()==$recruteurCheck->getMdp())
            {
                $session= new Session();
                $session->set('id',$recruteurCheck->getId());
                $session->set('nom',$recruteurCheck->getNom());
                $session->set('type',$recruteurCheck->getType());
                $session->set('mail',$recruteur->getMail());
                $session->set('competence',$recruteurCheck->getCompetence());
            }

        }
        return $this->render('front/index.html.twig', [
            'offres'=>$offreRepository->findAll(),
            'categories' => $categorieRepository->findAll(),
            'form' => $form->createView(),

        ]);
    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route ("addForum",name="addForum")
     */
    public function addForum(Request $request, RecruteurRepository $repository)
    {
        $forum = new Forum();
        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $value=$repository->find($this->get('session')->get('id'));
            $forum->setRecruteur($value);
            $em = $this->getDoctrine()->getManager();
            $em->persist($forum);
            $em->flush();
            return $this->redirectToRoute('AfficheForum');

        }
        return $this->render('/front/forum/addForum.html.twig', array(
            'form' => $form->createView()
        ));


    }

    /**
     * @param ForumRepository $repository
     * @param Request $request
     * @param Forum $forum
     * @Route("/AfficheForum",name="AfficheForum")
     */
    public function paginationP(ForumRepository $repository,Request $request)
    {
        $limit=1;
        $page=(int)$request->query->get("page",1);
        $comm=$repository->paginatedAnnonces($page,$limit);
        $total=$repository->getTotalAnnonces();


        return $this->render('/front/forum/index.html.twig',[
            'pagination' => true,
            'forum'=> $comm,
            'total'=>$total,
            'limit'=>$limit,
            'page'=>$page

        ]);


    }
    /**
     * @param ForumRepository $repository
     * @param Request $request
     * @return Response
     * @Route ("forum/searchs", name="rechercheForum")
     */

    function SearchS (ForumRepository $repository,Request $request) {
        $sujet=$request->get('search');
        $forum=$repository->searchS($sujet);
        return $this->render('/front/forum/index.html.twig', [
            'pagination' => false,
            'forum'=>$forum
        ]);
    }
    /**
     * @param $id
     * @param ForumRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/supp/{id}" , name="d")
     */
    function deleteForum($id, ForumRepository $repository)
    {
        $forum = $repository->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($forum);
        $em->flush();
        return $this->redirectToRoute('AfficheForum');


    }
    /**
     * @param ForumRepository $repository
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route ("/UpdateForum/{id}", name="u")
     */

    function updateForum(ForumRepository $repository, $id, Request $request)
    {
        $forum = $repository->find($id);
        $form = $this->createForm(ForumType::class, $forum);
        $form->add('modifier',SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('AfficheForum');

        }
        return $this->render('front/forum/updateForum.html.twig', ['form' => $form->createView()]);


    }
    /**
     *
     *  @param Request $request
     *  @param Forum $forum
     * @Route("/forum/{id}/show", name="forum_show")
     */
    public function show8( Request $request, Forum $forum,RecruteurRepository $repository)
    {
        $comm = new Commenter();
        $comm->setForum($forum);
        $form = $this -> createForm(CommenterType::class, $comm);
        $form -> handleRequest($request);
        if ($form -> isSubmitted() and $form -> isValid()) {
            $value=$repository->find($this->get('session')->get('id'));
            $comm->setRecruteur($value);
            $em = $this -> getDoctrine() -> getManager();
            $em -> persist($comm);
            $em -> flush();
            return $this -> redirectToRoute('forum_show', ['id' => $forum->getId() ]);

        }


        return $this->render('/front/forum/show.html.twig', [
            'forum' => $forum,
            'form' => $form->createView()
        ]);


    }
    /**
     * @param $ref
     * @param CommenterRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/suppCommentaire/{ref}" , name="dcf")
     */
    function deleteComm($ref, CommenterRepository $repository)
    {
        $comm = $repository->find($ref);
        $id=$comm->getForum()->getId();

        $em = $this->getDoctrine()->getManager();
        $em->remove($comm);
        $em->flush();

        return $this->redirectToRoute('forum_show',array('id'=>$id));
    }

    /**
     * @param CommenterRepository $repository
     * @param $ref
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route ("UpdateComm/UpdateComm/{ref}", name="UpdateComm")
     */

    function updateComm (CommenterRepository $repository, $ref, Request $request)
    {
        $comm = $repository->find($ref);
        $id=$comm->getForum()->getId();
        $form = $this->createForm(CommenterType::class, $comm);
        $form->add('modifier',SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('forum_show',array('id'=>$id));

        }
        return $this->render('front/commenter/updateComm.html.twig', ['form' => $form->createView()]);


    }


    /**
     * @param ForumRepository $repository
     * @return Response
     * @Route ("forum/tri")
     */

    function OrderBysujetsQL (ForumRepository $repository) {
        $forum=$repository->OrderBysujetQB ();
        return $this->render('front/forum/index.html.twig', ['forum'=>$forum]);
    }

    /**
     * @param EvenementRepository $repository
     * @param Request $request
     * @return Response
     * @Route("/rechercheevent",name="rechercheevent")
     */

    function RechercheEvent(EvenementRepository $repository , Request $request)
    {
        $nom=$request->get('recherche');
        $event=$repository->RechercheNom($nom);
        return $this->render('/front/Event/recherche.html.twig' , ['event'=>$event]);
    }
    /**
     * @Route("accepte/{id}",name="accepte", methods={"GET","POST"})
     */
    public function accepte(PostulerRepository $postulerRepository,$id,RecruteurRepository $recruteurRepository):Response
    {
        $test = "accepter";
        $entityManager = $this->getDoctrine()->getManager();
        $vale = $recruteurRepository->find($id);
        $val = $entityManager->getRepository(Postuler::class)->findOneBy(['recruteur'=>$vale]);
        $postuler = $entityManager->getRepository(Postuler::class)->find($val);
        $val->setAccepte($test);
        $entityManager->flush();
        return $this->render('/front/offre/content.html.twig');
    }
    /**
     * @Route("confirmer/{id}",name="confirmer", methods={"GET","POST"})
     */
    public function confirmer(\Swift_Mailer $mailer,PostulerRepository $postulerRepository,$id,RecruteurRepository $recruteurRepository): Response
    {

        $val = $recruteurRepository->find($id);
        $postuler = $postulerRepository->findBy(['recruteur'=>$val]);
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('/front/offre/listP.html.twig', [
            'postulers' => $postuler,
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A3', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();
        // Store PDF Binary Data
        $output = $dompdf->output();

        // In this case, we want to write the file in the public directory
        $publicDirectory = $this->getParameter('upload_directory');
        // e.g /var/www/project/public/mypdf.pdf
        $pdfFilepath =  $publicDirectory . '/mypdf.pdf';

        // Write file to the desired path
        file_put_contents($pdfFilepath, $output);

        // Send some text response

        $message = (new \Swift_Message('Confirmation for offer'))
            ->setFrom('noreplay.espritwork@gmail.com')
            ->setTo($val->getMail())
            ->setBody(
                $this->renderView(
                // templates/front/offre/confirm.html.twig
                    '/front/offre/confirm.html.twig'
                    , [
                    'postulers' => $postuler,
                ]),
                'text/html'
            )
            ->attach(\Swift_Attachment::fromPath($pdfFilepath))
        ;
        $mailer->send($message);
        return $this->redirectToRoute('update');
    }
    /**
     * @Route("ajoutevent",name="ajoutevent")
     */
    public function ajoutEvent(Request $request , \Swift_Mailer $mailer,RecruteurRepository $repository)
    {
        $event= new Evenement();
        $form=$this->createForm(EvenementType::class,$event);
        $form= $form->handleRequest($request);

        if($form->isSubmitted() and $form->isValid()){
            $value=0;
            $event->setJaime($value);
            $event->setJaimepas($value);
            $event->setNbp($value);
            $rc = $repository->find($this->get('session')->get('id'));
            $event->setIdrecruteur($rc);
            $em=$this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();
            $event = $form->getData();
            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('noreplay.espritwork@gmail.com')
                ->setTo($event->getEmail())
                ->setBody(
                    $this->renderView(
                    // templates/emails/registration.html.twig
                        '/front/Event/registration.html.twig',
                        compact('event')
                    ),
                    'text/html'
                )
            ;
            $mailer->send($message);



            return $this->redirectToRoute('afficheevent');
        }
        return $this->render('/front/Event/ajoutE.html.twig',['form'=>$form->createView()]);
    }
    /**
     * @Route("/addrecla", name="addrecla")
     */
    public function addrecla(Request $request,ReclamationRepository $reclamationRepository,RecruteurRepository $recruteurRepository): Response
    {
        $r =$recruteurRepository->find($this->get('session')->get('id'));
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class,$reclamation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation->setRecruteur($r);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reclamation);
            $entityManager->flush();
            return $this->redirectToRoute('front');
        }
        return $this->render('/front/newreclamation.html.twig', [
            'reclamations' => $reclamationRepository->findAll(),'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/afficheevent", name="afficheevent")
     */
    public function afficheEvent(EvenementRepository $repository , Request $request , PaginatorInterface $paginator )
    {
        $event=$repository->findAll();
        $event=$repository->OrderByNom();


        $event = $paginator->paginate(

            $event,//on passe les donnees
            $request->query->getInt('page',1),
            4

        );




        return $this->render('/front/Event/afficheE.html.twig',compact('event'));
    }


    /**
     * @Route("/filterevent", name="filterevent")
     */
    public function filterEvent(EvenementRepository $repository , Request $request , PaginatorInterface $paginator )
    {
        $event=$repository->findAll();
        $event=$repository->Cat();


        $event = $paginator->paginate(

            $event,//on passe les donnees
            $request->query->getInt('page',1),
            4

        );
        return $this->render('/front/Event/afficheE.html.twig',compact('event'));
    }






    /**
     * @param $id
     * @param EvenementRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/supprimeevent/{id}" , name="supprimeevent")
     */

    function supprimerEvent($id , EvenementRepository $repository){
        $event=$repository->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($event);
        $em->flush();
        return $this->redirectToRoute('afficheevent');
    }

    /**
     * @param EvenementRepository $repository
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/modifieevent/{id}" , name="modifieevent")
     */

    function modifierEvent(EvenementRepository $repository ,$id , Request $request)

    {

        $event=$repository->find($id);
        $form=$this->createForm(EvenementType::class,$event);
        $form->add('Modifier',SubmitType::class);
        $form->handleRequest($request);
        if ( $form->isSubmitted() && $form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('afficheevent');
        }

        return $this->render('/front/Event/modifieE.html.twig' , ['form'=>$form->createView()] );

    }




    /**
     * @Route("/participeevent/{id}", name="participeevent")
     */
    public function participeEvent(EvenementRepository $repository , $id , Request $request)
    {
        $event=$repository->find($id);
        $new_nb=$event->getNbp() + 1;
        $event->setNbp($new_nb);
        $this->getDoctrine()->getManager()->flush();
        //return $this->render('home/afficheE.html.twig', ['event' => $event]);

        $request
            ->getSession()
            ->getFlashBag()
            ->add('participe', ' Votre participation est enregistre avec succes');


        return $this->redirectToRoute('afficheevent');
    }


    /**
     * @param $id
     * @param EvenementRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/likeevent/{id}", name="likeevent")
     */
    public function likeEvent(EvenementRepository $repository , $id )
    {
        $event=$repository->find($id);
        $new=$event->getJaime() + 1;
        $event->setJaime($new);
        $this->getDoctrine()->getManager()->flush();
        //return $this->render('home/afficheE.html.twig', ['event' => $event]);

        return $this->redirectToRoute('afficheevent');
    }

    /**
     * @param $id
     * @param EvenementRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/dislikeevent/{id}", name="dislikeevent")
     */
    public function dislikeEvent(EvenementRepository $repository , $id )
    {
        $event=$repository->find($id);
        $new=$event->getJaimepas() + 1;
        $event->setJaimepas($new);
        $this->getDoctrine()->getManager()->flush();
        //return $this->render('home/afficheE.html.twig', ['event' => $event]);

        return $this->redirectToRoute('afficheevent');
    }
    /**
     * @Route("/Logout", name="Logout")
     */
    public function Logout(Request $request)
    {
        $session = $request->getSession();
        $session->clear();
        return $this->redirectToRoute('front');
    }
    /**
     * @Route("/offredelete1/", name="post", methods={"GET","POST"})
     */
    public function post(Request $request, Offre $offre,RecruteurRepository $repository): Response
    {
        $offre = new Offre();
        $value=$repository->find($this->get('session')->get('id'));

        return $this->redirectToRoute('offre1');
    }
    /**
     * @Route("/addrec", name="addrec", methods={"GET","POST"})
     */
    public function addrec(Request $request,RecruteurRepository $repository): Response
    {
        $recruteur = new Recruteur();
        $form = $this->createForm(CreateType::class, $recruteur);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['photo']->getData();
            $filename = md5(uniqid()).'.'.$uploadedFile->guessExtension();
            $uploadedFile->move($this->getParameter('upload_directory'),$filename);
            $recruteur->setPhoto($filename);
            $recruteur->setType('recruteur');
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($recruteur);
            $entityManager->flush();

            return $this->redirectToRoute('Login');
        }
        return $this->render('/front/recruteur_type.html.twig', [
            'recruteurs' => $repository->findAll(),'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/addcand", name="addcand", methods={"GET","POST"})
     */
    public function addcand(Request $request,RecruteurRepository $repository): Response
    {
        $recruteur = new Recruteur();
        $form = $this->createForm(CandidatType::class, $recruteur);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['photo']->getData();
            $filename = md5(uniqid()).'.'.$uploadedFile->guessExtension();
            $uploadedFile->move($this->getParameter('upload_directory'),$filename);
            $recruteur->setPhoto($filename);
            $recruteur->setType('candidat');
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($recruteur);
            $entityManager->flush();

            return $this->redirectToRoute('Login');
        }
        return $this->render('/front/candidat_type.html.twig', [
            'recruteurs' => $repository->findAll(),'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/addfreel", name="addfreel", methods={"GET","POST"})
     */
    public function addfreel(Request $request,RecruteurRepository $repository): Response
    {
        $recruteur = new Recruteur();
        $form = $this->createForm(FreelancerType::class, $recruteur);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['photo']->getData();
            $filename = md5(uniqid()).'.'.$uploadedFile->guessExtension();
            $uploadedFile->move($this->getParameter('upload_directory'),$filename);
            $recruteur->setPhoto($filename);
            $recruteur->setType('freelancer');
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($recruteur);
            $entityManager->flush();

            return $this->redirectToRoute('Login');
        }
        return $this->render('/front/freelancer/freelancer_type.html.twig', [
            'recruteurs' => $repository->findAll(),'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/freelancers",name="freelancers")
     *
     */
    public function freelancersAction(RecruteurRepository $repository){
       // $val = $repository->findAll();
       $recruteur=$repository->findBy(['type'=>'freelancer']);
        return $this->render("front/freelancer/freelancers.html.twig",[
            'recruteurs'=>$recruteur
        ]);
    }
    /**
     * @Route("/projet_index", name="projet_index", methods={"GET"})
     */
    public function projet_index(ProjetRepository $projetRepository): Response
    {
        return $this->render('front/projet/index.html.twig', [
            'projets' => $projetRepository->findAll(),
        ]);
    }
    /**
     * @Route("/addProjet", name="addProjet", methods={"GET","POST"})
     */
    public function addProjet(Request $request,RecruteurRepository $repository): Response
    {
        $value=$repository->find($this->get('session')->get('id'));
        $projet = new Projet();
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);
        $projet->setUser($value);
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['logo']->getData();
            $filename = md5(uniqid()).'.'.$uploadedFile->guessExtension();
            $uploadedFile->move($this->getParameter('upload_directory'),$filename);
            $projet->setLogo($filename);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($projet);
            $entityManager->flush();
            return $this->redirectToRoute('projet_index');
        }

        return $this->render('front/projet/_form.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/afficheProjets",name="afficheProjets")
     *
     */
    public function afficheProjets(ProjetRepository $repository){
        $projet=$repository->findAll();
        return $this->render("front/projet/show.html.twig",[
            "projets"=>$projet
        ]);
    }
    /**
     * @Route("/makeoffre", name="makeoffre", methods={"GET"})
     */

    public function makeoffre(Request $request,ProjetRepository $repository,\Swift_Mailer $mailer)
    {
            $projet=$repository->find($request->query->get("id"));
            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('noreplay.espritwork@gmail.com')
                ->setTo($projet->getUser()->getMail())
                ->setBody(
                    "Bonjour l'utilisateur ".$this->get('session')->get('nom')." vous contactez a propos votre offre : ".$projet->getNomProjet()
                );
            $mailer->send($message);

        $sid = 'AC42f5fedf86023f54e795245896d73179';
        $token = '5e4082f434692075f4561ab698301dfc';
          $client = new Client($sid, $token);

          $client->messages->create(
           '+21695417551',
                 [
              'from' => '+14049988809',
              'body' =>               'Bonjour lutilisateur  '.$this->get('session')->get('nom').' vous contactez a propos votre offre : '.$projet->getNomProjet()

           ]
          );
        $response = new Response("offre bien ajouter");
        return $response;

    }
    /**
     * @Route("/searchproject", name="searchproject", methods={"GET"})
     */

    public function search(Request $request,ProjetRepository $repository,\Swift_Mailer $mailer){

        $result = $repository->createQueryBuilder('o')
            ->where('o.nomProjet  LIKE :product')
            ->setParameter('product', '%'.$request->query->get("id").'%')
            ->getQuery()
            ->getArrayResult();
        return new JsonResponse([
                'projects' => $result
            ]

        );

    }
    /**
     * @Route("projet_edit/{id}/edit", name="projet_edit", methods={"GET","POST"})
     */
    public function projet_edit(Request $request, Projet $projet): Response
    {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['logo']->getData();
            $filename = md5(uniqid()).'.'.$uploadedFile->guessExtension();
            $uploadedFile->move($this->getParameter('upload_directory'),$filename);
            $projet->setLogo($filename);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('projet_index');
        }

        return $this->render('front/projet/edit.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("projet_delete/{id}", name="projet_delete", methods={"DELETE"})
     */
    public function projet_delete(Request $request, Projet $projet): Response
    {
        if ($this->isCsrfTokenValid('delete'.$projet->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($projet);
            $entityManager->flush();
        }


        return $this->redirectToRoute('projet_index');

    }

    /**
     * @Route("/json_getAllForum", name="json_getAllForum")
     */
    public function json_getAllForum(NormalizerInterface $normalizer): Response
    {
        $events = $this->getDoctrine()->getRepository(Forum::class)->findAll();
        $jsonContent = $normalizer->normalize($events, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @Route("/json_findForum/{id}", name="json_findForum")
     */
    public function json_findForum(Forum $forum, Request $request, NormalizerInterface $normalizer): Response
    {
        if (!$forum) {
            return new Response("forum Not Found");
        }
        $forums = [];
        array_push($forums, $forum);
        $jsonT =$normalizer->normalize($forums, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonT));
    }

    /**
     * @Route("/json_ajoutdForum/{rec}", name="json_ajoutdForum", methods={"POST"})
     */
    public function json_ajoutdForum(Request $request,$rec, SerializerInterface $serializer): Response
    {
        $content = $request->getContent();
        $rec =  $this->getDoctrine()->getRepository(Recruteur::class)->find($rec);
        $event = $serializer->deserialize($content, Forum::class, 'json');
        $event->setRecruteur($rec);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($event);
        $entityManager->flush();
        return new Response("Event Added");
    }


    /**
     * @Route("/json_updateForum/{id}/update", name="json_updateForum")
     */
    public function json_updateForum(Forum $event, Request $request): Response
    {

        if (!$event) {
            return new Response("Event Not Found");
        }
        $event->setTheme($request->get('theme'));
        $event->setSujet($request->get('sujet'));
        $event->setProbleme($request->get('probleme'));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();
        return new Response("Event Updated");
    }

    /**
     * @Route("/test/add2" , name="add_compte")
     */
    public function addTestt(Request $request, NormalizerInterface $normalizer){
        $em=$this->getDoctrine()->getManager();
        $test= new Test();
        $test->setNom($request->get('nom'));
        $test->setQ1($request->get('q1'));
        $test->setR1($request->get('r1'));
        $test->setQ2($request->get('q2'));
        $test->setR2($request->get('r2'));
        $test->setQ3($request->get('q3'));
        $test->setR3($request->get('r3'));
        $test->setQ4($request->get('q4'));
        $test->setR4($request->get('r4'));
        $test->setQ5($request->get('q5'));
        $test->setR5($request->get('r5'));
        $em->persist($test);
        $em->flush();
        $jsonContent=$normalizer->normalize($test,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }
    /**
     * @Route("/listeC", name="listeC")
     */

    public function getCategorie(CatRepository  $repository , SerializerInterface $serializer){

        $categorie = $repository->findAll();
        $json = $serializer->serialize($categorie,'json',['groups' => 'categorie']);
        //dump($json);
        //dump($categorie);
        //die;
        return new Response(json_encode($json));
    }

    /**
     * @Route("/listeC1", name="listeC1")
     */

    public function allEventJson(NormalizerInterface $normalizer){
        $repository=$this->getDoctrine()->getRepository(Cat::class);
        $categorie=$repository->findAll();
        $jsonContent=$normalizer->normalize($categorie,'json',['groups'=>'cat:read']);
        return new Response(Json_encode($jsonContent));

    }
    /******************Ajouter Freelancer*****************************************/
    /**
     * @Route("/addFreelancer", name="add_Freelancer")
     */

    public function ajouterFreelancerAction(Request $request)
    {
        $freelancer = new Freelancer();
        $email= $request->query->get("email");
        $name = $request->query->get("name");
        $password = $request->query->get("password");
        $photo = $request->query->get("photo");
        $title = $request->query->get("title");
        $skills = $request->query->get("skills");
        $country = $request->query->get("country");
        $prix = $request->query->get("prix");
        $experience = $request->query->get("experience");
        $em = $this->getDoctrine()->getManager();

        $freelancer->setEmail($email);
        $freelancer->setName($name);
        $freelancer->setPassword($password);
        $freelancer->setPhoto($photo);
        $freelancer->setTitle($title);
        $freelancer->setSkills($skills);
        $freelancer->setCountry($country);
        $freelancer->setPrix($prix);

        $freelancer->setExperience($experience);

        $em->persist($freelancer);
        $em->flush();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($freelancer);
        return new JsonResponse($formatted);

    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/delete", name="freelancer_del")
     */

    public  function delete2(Request $request){
        $id = $request->get("id");

        $em = $this->getDoctrine()->getManager();

        $freelancer = $em->getRepository(Freelancer::class)->find($id);

        if($freelancer!=null){
            $em->remove($freelancer);
            $em->flush();

            $serielize = new Serializer([new ObjectNormalizer()]);
            $formatted = $serielize->normalize("freelancer supprimé avec success");
            return new JsonResponse($formatted);
        }
        return new JsonResponse("id freelancer invalide");
    }


    /******************affichage Freelancer*****************************************/

    /**
     * @Route("/displayFreelancer", name="display_freelancer")
     */
    public function allRecAction()
    {

        $reclamation = $this->getDoctrine()->getManager()->getRepository(Freelancer::class)->findAll();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($reclamation);

        return new JsonResponse($formatted);

    }

    /******************Modifier Freelancer*****************************************/
    /**
     * @Route("/updateFreelancer", name="update_freelancer")
     */
    public function modifierFreelancerAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $freelancer = $this->getDoctrine()->getManager()
            ->getRepository(Freelancer::class)
            ->find($request->get("id"));

        $freelancer->setEmail($request->get("email"));
        $freelancer->setName($request->get("name"));
        $freelancer->setPassword($request->get("password"));
        $freelancer->setPhoto($request->get("photo"));
        $freelancer->setTitle($request->get("title"));
        $freelancer->setSkills($request->get("skills"));
        $freelancer->setCountry($request->get("country"));
        $freelancer->setPrix($request->get("prix"));
        $freelancer->setExperience($request->get("experience"));

        $em->persist($freelancer);
        $em->flush();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($freelancer);
        return new JsonResponse("Freelancer a ete modifiee avec success.");

    }

    /******************Detail Freelancer*****************************************/

    /**
     * @Route("/detailFreelancer", name="detail_reclamation")
     */

    //Detail Freelancer
    public function detailFreelancerAction(Request $request)
    {
        $id = $request->get("id");

        $em = $this->getDoctrine()->getManager();
        $reclamation = $this->getDoctrine()->getManager()->getRepository(Freelancer::class)->find($id);
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getDescription();
        });
        $serializer = new Serializer([$normalizer], [$encoder]);
        $formatted = $serializer->normalize($reclamation);
        return new JsonResponse($formatted);
    }
    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     * @Route("/addC", name="addC")
     */

    public function addCategorie(Request $request , SerializerInterface $serializer ){
        $em = $this->getDoctrine()->getManager();
        $content = $request->getContent();
        $data = $serializer->deserialize($content,Categorie::class,'json');
        $em->persist($data);
        $em->flush();
        return new Response('Categorie ajoute avec succes');
    }

    /**
     * @param Request $request
     * @param NormalizerInterface $normalizer
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route("/addC1", name="addC1")
     */

    public function addCat(Request $request, NormalizerInterface $normalizer){
        $em= $this->getDoctrine()->getManager();
        $categorie = new Categorie();
        $categorie->setNom($request->get('nom'));
        //$reclamations->setType($request->get('type'));
        $em->persist($categorie);
        $em->flush();
        $json_content=$normalizer->normalize($categorie,'json',['groups'=>'cat:read']);
        return new Response("Categorie ajoutée avec succes".json_encode($json_content));

    }



    /**
     * @param Request $request
     * @param NormalizerInterface $normalizer
     * @param $id
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route("/deleteC/{id}", name="deleteC")
     */

    public function deleteCategorie(Request $request, NormalizerInterface $normalizer, $id){

        $em=$this->getDoctrine()->getManager();
        $categorie = $em->getRepository(Categorie::class)->find($id);
        $em->remove($categorie);
        $em->flush();
        $json_content=$normalizer->normalize($categorie,'json',['groups'=>'cat:read']);
        return new Response("Categorie supprimé avec succes".json_encode($json_content));

    }

    /**
     * @param Request $request
     * @param NormalizerInterface $normalizer
     * @param $id
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route("/updateC/{id}", name="updateC")
     */

    public function updateCategorie(Request $request,NormalizerInterface $normalizer,$id){
        $em= $this->getDoctrine()->getManager();
        $categorie=$em->getRepository(Categorie::class)->find($id);
        $categorie->setNom($request->get('nom'));
        $em->flush();
        $json_content=$normalizer->normalize($categorie,'json',['groups'=>'cat:read']);
        return new Response("Categorie modifié avec succes".json_encode($json_content));

    }


    /**
     * @Route("/liste", name="liste")
     */

    public function getEvenements(EvenementRepository $repository , SerializerInterface $serializer){

        $evenement = $repository->findAll();
        $json = $serializer->serialize($evenement,'json',['groups' => 'evenement']);
        dump($json);
        die;
    }

    /**
     * @param NormalizerInterface $normalizer
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route("/listeE", name="listeE")
     */
    public function listeE(NormalizerInterface $normalizer){
        $repository=$this->getDoctrine()->getRepository(Evenement::class);
        $evenement=$repository->findAll();
        $jsonContent=$normalizer->normalize($evenement,'json',['groups'=>'evenement:read']);
        return new Response(Json_encode($jsonContent));

    }


    /**
     * @param Request $request
     * @param NormalizerInterface $normalizer
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route("/addE1", name="addE1")
     */

    public function addEvenement(Request $request, NormalizerInterface $normalizer){
        $em= $this->getDoctrine()->getManager();
        $evenement = new Evenement();
        $evenement->setNom($request->get('nom'));

        $date = $request->query->get('date');
        //$rec = $request->query->get('idrecruteur');
        $evenement->setDate(new \DateTime($date));
        //$recu = $em->getRepository(Evenement::class)->find($rec);
        $evenement->setDescription($request->get('description'));
        $evenement->setEmail($request->get('email'));
        //$evenement->setIdrecruteur($recu);
        //$evenement->setNbEtoile(0);
       // $evenement->setNbVote(0);
        //$reclamations->setType($request->get('type'));
        $em->persist($evenement);
        $em->flush();
        $json_content=$normalizer->normalize($evenement,'json',['groups'=>'evenement:read']);
        return new Response("Evenement ajoutée avec succes".json_encode($json_content));

    }

    /**
     * @param Request $request
     * @param NormalizerInterface $normalizer
     * @param $id
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route("/deleteE/{id}", name="deleteE")
     */

    public function deleteEvenement(Request $request, NormalizerInterface $normalizer, $id){

        $em=$this->getDoctrine()->getManager();
        $evenement = $em->getRepository(Evenement::class)->find($id);
        $em->remove($evenement);
        $em->flush();
        $json_content=$normalizer->normalize($evenement,'json',['groups'=>'evenement:read']);
        return new Response("Evenement supprimé avec succes".json_encode($json_content));

    }

    /**
     * @param Request $request
     * @param NormalizerInterface $normalizer
     * @param $id
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route("/updateE/{id}", name="updateE")
     */


    public function updateEvenement(Request $request,NormalizerInterface $normalizer,$id){
        $em= $this->getDoctrine()->getManager();
        $evenement=$em->getRepository(Evenement::class)->find($id);
        $evenement->setNom($request->get('nom'));
        $date = new \DateTime();
        $evenement->setDate($date);
        $evenement->setDescription($request->get('description'));
        $evenement->setEmail($request->get('email'));

        $em->flush();
        $json_content=$normalizer->normalize($evenement,'json',['groups'=>'evenement:read']);
        return new Response("Evenement modifié avec succes".json_encode($json_content));

    }

    /**
     * @param Request $request
     * @param NormalizerInterface $normalizer
     * @param $id
     * @return Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @Route ("/rateE/{id}")
     */
    public function udeateevrate(Request $request,NormalizerInterface $normalizer,$id){
        $em= $this->getDoctrine()->getManager();
        $evenement=$em->getRepository(Evenement::class)->find($id);
        $evenement->setNom($request->get('nom'));
        $date = new \DateTime();
        $evenement->setDate($date);
        $evenement->setDescription($request->get('description'));
        $evenement->setEmail($request->get('email'));
        $evenement->setNbEtoile($request->get('nbetoile'));
        $evenement->setNbVote($request->get('nbvote'));


        $em->flush();
        $json_content=$normalizer->normalize($evenement,'json',['groups'=>'evenement:read']);
        return new Response("Evenement modifié avec succes".json_encode($json_content));

    }
    /**
     * @Route("/deletet/{id}", name="deletetransportt")
     */
    public function deleteTransport(Request $request,NormalizerInterface $normalizer,$id): Response
    {
        $em= $this->getDoctrine()->getManager();
        $test= $em->getRepository(Test::class)->find($id);
        $em->remove($test);
        $em->flush();

        $jsonContent = $normalizer->normalize($test,'json',['groups'=>'post:read']);
        return new Response("test deleted successfully".json_encode($jsonContent));
    }

    /**
     * @Route("/json_deleteForum/{id}", name="json_deleteForum", methods={"DELETE"})
     */
    public function json_deleteForum(Forum $event, Request $request): Response
    {
        if (!$event) {
            return new Response("User Not Found");
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($event);
        $entityManager->flush();
        return new Response("Event deleted");
    }
    /**
     * @Route("/json_getCommenter", name="json_getCommenter")
     */
    public function json_getCommenter(NormalizerInterface $normalizer): Response
    {
        $events = $this->getDoctrine()->getRepository(Commenter::class)->findAll();
        foreach ($events as $event) {
            $event->setForumId($event->getForum()->getId());
        }
        $jsonContent = $normalizer->normalize($events, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }
    /**
     * @Route("/json_ajoutCommenter/{id}/{rec}", name="json_ajoutCommenter", methods={"POST"})
     */
    public function json_ajoutCommenter($id,$rec, Request $request, SerializerInterface $serializer): Response
    {
        $content = $request->getContent();
        $event = $serializer->deserialize($content, Commenter::class, 'json');
        $forum = $this->getDoctrine()->getRepository(Forum::class)->find($id);
        $rec =  $this->getDoctrine()->getRepository(Recruteur::class)->find($rec);
        $event->setForum($forum);
        $event->setRecruteur($rec);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($event);
        $entityManager->flush();
        return new Response("Event Added");
    }

    /**
     * @Route("/json_ajoutCommenterupdate/{id}/update", name="json_ajoutCommenterupdate")
     */
    public function json_ajoutCommenterupdate(Commenter $event, Request $request): Response
    {

        if (!$event) {
            return new Response("Event Not Found");
        }
        $event->setCommentaire($request->get('commentaire'));
        $event->setRating($request->get('rating'));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();
        return new Response("Event Updated");
    }

    /**
     * @Route("/json_deleteCommenter/{id}", name="json_deleteCommenter", methods={"DELETE"})
     */
    public function json_deleteCommenter(Commenter $event, Request $request): Response
    {
        if (!$event) {
            return new Response("User Not Found");
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($event);
        $entityManager->flush();
        return new Response("Event deleted");
    }
    /**
     * @Route("/calendar", name="calendar_index")
     */
    public function calendar_index(CalendarRepository $repository): Response
    {
        return $this->render('front/calendar/index.html.twig', [
            'calendars' => $repository->findAll(),
        ]);
    }
    /**
     * @Route("/calendar/aff", name="calendar1_index")
     */
    public function calendar1_index(CalendarRepository $calendar): Response
    {
        $events = $calendar->findAll();

        $rdvs = [];

        foreach($events as $event){
            $rdvs[] = [
                'id' => $event->getId(),
                'start' => $event->getStart()->format('Y-m-d H:i:s'),
                'end' => $event->getEnd()->format('Y-m-d H:i:s'),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'backgroundColor' => $event->getBackground(),
                'borderColor' => $event->getBorderColor(),
                'textColor' => $event->getTextColor(),
                'allDay' => $event->getAllDay(),
            ];
        }

        $data = json_encode($rdvs);
        return $this->render('front/calendar/aff.html.twig',compact('data'));
    }

    /**
     * @Route("/calendar/new", name="calendar_new", methods={"GET","POST"})
     */
    public function calendar_new(Request $request): Response
    {
        $calendar = new Calendar();
        $form = $this->createForm(CalendarType::class, $calendar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($calendar);
            $entityManager->flush();

            return $this->redirectToRoute('calendar1_index');
        }

        return $this->render('front/calendar/new.html.twig', [
            'calendars' => $calendar,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/calendar/show/{id}", name="calendar_show", methods={"GET"})
     */
    public function calendar_show(Calendar $calendar): Response
    {
        return $this->render('front/calendar/show.html.twig', [
            'calendars' => $calendar,
        ]);
    }
    /**
     * @Route("/{id}/calendar/edit", name="calendar_edit", methods={"GET","POST"})
     */
    public function calendar_edit(Request $request, Calendar $calendar): Response
    {
        $form = $this->createForm(CalendarType::class, $calendar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('calendar_index');
        }

        return $this->render('front/calendar/edit.html.twig', [
            'calendars' => $calendar,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/calendar/del/{id}", name="calendar_delete", methods={"DELETE"})
     */
    public function calendar_delete(Request $request, Calendar $calendar): Response
    {
        if ($this->isCsrfTokenValid('delete'.$calendar->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($calendar);
            $entityManager->flush();
        }

        return $this->redirectToRoute('calendar_index');
    }
    /**
     * @return Response
     * @Route("/front/listT/", name="test_1", methods={"GET"})
     */
    public function test_1(TestRepository $testRepository): Response
    {
        return $this->render('front/test/testT.html.twig', [
            'tests' => $testRepository->findAll(),
        ]);
    }
    /**
     * @Route("/front/obtCertif/{id}", name="obtCertif", methods={"GET","POST"})
     */
    public function obtCertif(Certificat $certificat,CertificatRepository  $repository,$id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $certificat = $entityManager->getRepository(Certificat::class)->find($id);
        $rec = $entityManager->getRepository(Recruteur::class)->find($id);
        $certificat->setIdrecruteur($rec);
        $entityManager->flush();
        $certificat=$repository->findBy(['idrecruteur' => $rec->getId()]);

        return $this->render('front/test/endTest.html.twig',[
            "certificats" => $certificat
        ]);
    }
    /**
     * @Route("/front/listo2/cer/{id}", name="listo2", methods={"GET"})
     */
    public function listo2(CertificatRepository  $certificat,$id): Response
    {
        $p =$certificat->find($id);
        $nom =$p->getNom();
        //$user=$this->getUser()->getUsername();
        //;
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('front/certificat/listo2.html.twig', [
            'certificat' => $p,
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A3', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (inline view)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => false
        ]);
    }

    /**
     * @Route("/front/trouver/{id}", name="trouver")
     */
    public function Valider(Request $request,$id,TestRepository $repositorys)
    {
        $test= $repositorys->findBy(
            ['id'=> $id]
        );
        return $this->render('front/test/newFront.html.twig', [
            'tests' => $test,
        ]);//liasion twig avec le controller
    }
    /**
     * @Route("/front/trouver2/{id}", name="trouver2")
     */
    public function Correct(Request $request,$id,TestRepository $repositorys)
    {

        $repository = $this->getDoctrine()->getrepository(Certificat::Class);//recuperer repisotory


        $certificat = $repository->findBy(
            ['test' => $id]
        );
        return $this->render('front/test/congrats.html.twig', [
            'certificats' => $certificat,
        ]);//liasion twig avec le controller

    }
    /**
     * @Route("/json_details/{id}", name="json_details", methods={"GET","POST"})
     */
    public function json_details(OffreRepository  $offreRepository,NormalizerInterface $normalizer,RecruteurRepository $repository,PostulerRepository $postulerRepository,$id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $offre = $entityManager->getRepository(Offre::class)->find($id);
        $value = $offre->getAbn();
        $value = $value + 1 ;
        $offre->setAbn($value);
        $entityManager->flush();
        $offretype = $offreRepository->findBy(['id' => $id]);
        $jsonContent = $normalizer->normalize($offretype, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }
    /**
     * @Route("/json_getpos/{offre}", name="json_getpos", methods={"GET","POST"})
     */
    public function json_getpos(NormalizerInterface $normalizer,$offre,PostulerRepository $repository): Response
    {
        $pos = $repository->findBy(['offre'=>$offre]);
        $jsonContent = $normalizer->normalize($pos, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }
    /**
     * @Route("/json_getcom/{id}", name="json_getcom", methods={"GET","POST"})
     */
    public function json_getcom(NormalizerInterface $normalizer,$id,CommentRepository $repository): Response
    {
        $com = $repository->findBy(['offre'=>$id]);
        $jsonContent = $normalizer->normalize($com, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }
    /**
     * @Route("/json_getOffreRec/{nom}", name="json_getOffreRec", methods={"GET","POST"})
     */
    public function json_getOffreRec(NormalizerInterface $normalizer,$nom,OffreRepository $repository): Response
    {
        $off = $repository->findBy(['nom'=>$nom]);
        $em = $this->getDoctrine()->getManager();
        $offre = new Offre();
        $offre->setAbn(1);
        $jsonContent = $normalizer->normalize($off, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }
    /**
     * @Route("/json_isliked/{offre}/{rec}", name="json_isliked", methods={"GET","POST"})
     */
    public function json_isliked(NormalizerInterface $normalizer,$offre,$rec,PostulerRepository $repository): Response
    {
        $pos = $repository->findBy(['offre'=>$offre,'recruteur'=>$rec]);
        $jsonContent = $normalizer->normalize($pos, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }
    /**
     * @Route("/json_getcat/{nom}", name="json_getcat", methods={"GET","POST"})
     */
    public function json_getcat(NormalizerInterface $normalizer,$nom,CategorieRepository $repository): Response
    {
        $jsonContent = $normalizer->normalize($repository->findBy(['nom'=>$nom]), 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }
    /**
     *
     * @Route("/json_addjob/{nom}/{email}/{title}/{description}/{uploadedFile}/{cat}/{rec}",name="json_addjob")
     */
    public function json_addjob(CategorieRepository $categorieRepository,RecruteurRepository $repository,NormalizerInterface $Normalizer,$nom,$email,$title,$description,$uploadedFile,$cat,$rec)
    {
        $value=$repository->find($rec);
        $categ = $categorieRepository->find($cat);
        $em = $this->getDoctrine()->getManager();
        $offre = new Offre();
        $offre->setNom($nom);
        $offre->setEmail($email);
        $offre->setTitle($title);
        $offre->setLogo($uploadedFile);
        $offre->setDescription($description);
        $offre->setIdrecruteur($value);
        $offre->setIdcategoriy($categ);
        $offre->setAbn(0);
        $em->persist($offre);
        $em->flush();
        $jsonContent = $Normalizer->normalize($offre, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }
    /**
     * @Route("/addjob", name="addjob", methods={"GET","POST"})
     */
    public function addjob(CategorieRepository $categorieRepository,Request $request,RecruteurRepository $repository,NormalizerInterface $normalizer): Response
    {
        $offre = new Offre();
        $value=$repository->find($this->get('session')->get('id'));
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['logo']->getData();
            $filename = md5(uniqid()).'.'.$uploadedFile->guessExtension();
            $uploadedFile->move($this->getParameter('upload_directory'),$filename);
            $offre->setLogo($filename);
            $offre->setIdrecruteur($value);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($offre);
            $entityManager->flush();

            return $this->redirectToRoute('offre1');
        }
        $jsonContent = $normalizer->normalize($offre,'json',['groups'=>'post:read']);
        return $this->render('/front/addjob.html.twig', [
            'categories' => $categorieRepository->findAll(),'form' => $form->createView(),
            'json_offre'=>$jsonContent,
        ]);
    }
    /**
     * @Route("/profil_det", name="profil_det", methods={"GET"})
     */
    public function profil_det(RecruteurRepository $recruteurRepository): Response
    {
        return $this->render('/front/profil_det.html.twig', [
            'recruteurs' => $recruteurRepository->findAll(),
        ]);
    }
    /**
     *
     * @Route("/json_offre1",name="json_offre1")
     */
    public function json_offre1(NormalizerInterface  $Normalizer,OffreRepository $offreRepository)
    {
        $of = $offreRepository->findAll();
        $jsonContent = $Normalizer->normalize($of, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @Route("/offre1", name="offre1", methods={"GET"})
     */
    public function offre1(OffreRepository $offreRepository,NormalizerInterface $normalizer): Response
    {
        $of = $offreRepository->findAll();
        return $this->render('/front/offre.html.twig', [
            'offres' => $offreRepository->findAll(),
        ]);
    }
    /**
     * @Route("/type/{type}", name="type", methods={"GET"})
     */
    public function Type(OffreRepository $offreRepository,$type): Response
    {
        $offretype = $offreRepository->findBy(['idcategoriy' => $type]);
        return $this->render('/front/type.html.twig', [
            'offres' => $offretype,
        ]);
    }
    /**
     * @Route("/pos/{id}", name="pos", methods={"GET","POST"})
     */
    public function pos(OffreRepository  $offreRepository,RecruteurRepository $repository,PostulerRepository $postulerRepository,$id): Response
    {
        $pos = $postulerRepository->findBy(['offre'=>$id]);
        return $this->render('/front/membre.html.twig', [
            'postulers' => $pos,
        ]);
    }
    /**
     * @Route("/login", name="Login")
     */
    public function login(Request $request,RecruteurRepository $repository)
    {
        $recruteur = new Recruteur();
        $form=$this->createForm(RecruteurType::class,$recruteur);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recruteurCheck = $repository->findOneBy(['mail' => $recruteur->getMail()]);
            if($recruteur->getMdp()==$recruteurCheck->getMdp())
            {
                $session= new Session();
                $session->set('id',$recruteurCheck->getId());
                $session->set('nom',$recruteurCheck->getNom());
                $session->set('mail',$recruteur->getMail());
                $session->set('type',$recruteurCheck->getType());
            }
        }
        return $this->render('/front/login.html.twig', [
            'form' => $form->createView(),
        ]);

    }
    /**
     * @Route("/make/{id}", name="make", methods={"GET","POST"})
     */
    public function make(OffreRepository $offreRepository,$id,Request $request,RecruteurRepository $repository,CommentRepository $commentRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $offre = $entityManager->getRepository(Offre::class)->find($id);
        $value = $offre->getAbn();
        $value = $value + 1 ;
        $offre->setAbn($value);
        $entityManager->flush();
        $comment = new Comment();
        $session = $request->getSession();
        $form1 = $this->createForm(CommentType::class,$comment);
        $form1->handleRequest($request);
        if ($form1->isSubmitted() && $form1->isValid()) {
            $comment->setCreatedAt(new \DateTime())
                ->setOffre($offre)
                ->setAuthorName($this->get('session')->get('mail'));
            $value=$repository->find($this->get('session')->get('id'));
            $comment->setIdrecruteur($value);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('make',['id'=>$id]);
        }
        $recruteur = new Recruteur();
        $form=$this->createForm(RecruteurType::class,$recruteur);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recruteurCheck = $repository->findOneBy(['mail' => $recruteur->getMail()]);
            if($recruteur->getMdp()==$recruteurCheck->getMdp())
            {
                $session= new Session();
                $session->set('id',$recruteurCheck->getId());
                $session->set('nom',$recruteurCheck->getNom());
                $session->set('type',$recruteurCheck->getType());
                $session->set('mail',$recruteur->getMail());
            }

        }
        $offretype = $offreRepository->findBy(['id' => $id]);
        $offrepost = $commentRepository->findBy(['offre'=>$offre]);
        return $this->render('/front/make.html.twig', [
            'comments'=> $offrepost,
            'offres' => $offretype,
            'form' => $form->createView(),
            'commentForm'=>$form1->createView(),
        ]);
    }

    /**
     * @Route("/update", name="update", methods={"GET","POST"})
     */
    public function up(OffreRepository  $offreRepository,RecruteurRepository $repository,CommentRepository $commentRepository): Response
    {
        $value=$repository->find($this->get('session')->get('id'));
        $offretype = $offreRepository->findBy(['idrecruteur' => $value]);
        $com = $commentRepository->findBy(['offre'=>$offretype]);
        return $this->render('/front/check.html.twig', [
            'offres' => $offretype,
            'comments'=> $com,
        ]);
    }

    /**
     * @Route("{id}", name="show1", methods={"GET"})
     */
    public function show(Categorie $categorie): Response
    {
        return $this->render('back/categorie/show.html.twig', [
            'categorie' => $categorie,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Categorie $categorie): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('categ');
        }

        return $this->render('back/categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("edit1/{id}", name="edit1", methods={"GET","POST"})
     */
    public function edit1(Request $request, Offre $offre,OffreRepository $offreRepository,$id): Response
    {
        /*$value =   $offre->getNb();
        $value ++ ;
        $offre->setNb($value);*/
        $offre=$offreRepository->find($id);
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['logo']->getData();

            $filename = md5(uniqid()).'.'.$uploadedFile->guessExtension();
            $uploadedFile->move($this->getParameter('upload_directory'),$filename);
            $offre->setLogo($filename);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('offre1');
        }
        return $this->render('front/offre/edit1.html.twig', [
            'offre' => $offre,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/offredelete1/{id}", name="offredelete1", methods={"DELETE"})
     */
    public function delete(Request $request, Offre $offre): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offre->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($offre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('offre1');
    }
    /**
     * @Route("/deletecom/{id}",name="deletecommentoffre", methods={"DELETE"})
     */
    public function deletecommentoffre(Request $request,Comment $comment): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('update');
    }
    /**
     * @Route("/deletecomperso/{id}",name="deletecomperso", methods={"DELETE"})
     */
    public function deletecomperso(Request $request,Comment $comment): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('offre1');
    }
    /**
     * @Route("/offre/{id}/like", name="post_like")
     * @param Offre $offre
     * @param ObjectManager $manager
     * @param PostulerRepository $postulerRepository
     * @param RecruteurRepository $repository
     * @return Response
     */
    public function like(Offre $offre,PostulerRepository $postulerRepository,RecruteurRepository $repository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user=$repository->find($this->get('session')->get('id'));
        if(!$user) return $this->json([
            'code'=>403,
            'message'=>"Unauthorized"
        ],403);
        if($offre->isLikedByRecruteur($user))
        {
            $like = $postulerRepository->findOneBy([
                'offre'=>$offre,
                'recruteur'=>$user
            ]);

            $entityManager->remove($like);
            $entityManager->flush();
            return $this->json([
                'code'=>200,
                'message'=>'like bien supprimé',
                'likes'=>$postulerRepository->count(['offre'=>$offre])
            ],200);
        }
        $like = new Postuler();
        $like->setOffre($offre);
        $like->setRecruteur($user);
        $entityManager->persist($like);
        $entityManager->flush();
    return  $this->json([
        'code'=>200,
        'message'=>'ca marche',
        'likes'=>$postulerRepository->count(['offre'=>$offre])
        ],200);
    }


    /**
     * @Route("/rec/add" , name="add_recruteur")
     */
    public function addUser(Request $request, NormalizerInterface $normalizer){
        $em=$this->getDoctrine()->getManager();
        $recruteur= new Recruteur();
        $recruteur->setNom($request->get('nom'));
        $recruteur->setPrenom($request->get('prenom'));
        $recruteur->setNomsociete($request->get('nomsociete'));
        $recruteur->setAdresse($request->get('adresse'));
        $recruteur->setMail($request->get('mail'));
        $recruteur->setNumsociete($request->get('numsociete'));
        $recruteur->setMdp($request->get('mdp'));
        $recruteur->setType($request->get('type'));
        $recruteur->setPhoto($request->get('photo'));
        $recruteur->setCompetence($request->get('competence'));
        $em->persist($recruteur);
        $em->flush();
        $jsonContent=$normalizer->normalize($recruteur,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }




    /**
     * @Route("/recruteur_listee",name="listeerec")
     */
    public function getrecruteurJSON(NormalizerInterface $Normalizer,RecruteurRepository $recruteurRepository): Response
    {
        $recruteur =$recruteurRepository->findAll();

        $jsonContent = $Normalizer->normalize($recruteur, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }



}

