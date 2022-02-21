<?php

namespace App\Controller;

use App\Entity\Cat;
use App\Entity\Categorie;
use App\Entity\Certificat;
use App\Entity\Forum;
use App\Entity\Offre;
use App\Entity\Projet;
use App\Entity\Recherche;
use App\Entity\Reclamation;
use App\Entity\Recruteur;
use App\Entity\Test;
use App\Form\CategorieType;
use App\Form\CatType;
use App\Form\CertificatType;
use App\Form\OffreType;
use App\Form\TestType;
use App\Repository\CategorieRepository;
use App\Repository\CatRepository;
use App\Repository\CertificatRepository;
use App\Repository\CommenterRepository;
use App\Repository\EvenementRepository;
use App\Repository\ForumRepository;
use App\Repository\OffreRepository;
use App\Repository\ProjetRepository;
use App\Repository\RechercheRepository;
use App\Repository\ReclamationRepository;
use App\Repository\RecruteurRepository;
use App\Repository\TestRepository;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Doctrine\DBAL\Types\TextType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Annotation\Groups;
class BackController extends AbstractController
{
    /**
     * @Route("/back", name="back")
     */
    public function index(): Response
    {
        return $this->render('back/index.html.twig', [
            'controller_name' => 'BackController',
        ]);
    }
    /**
     * @Route("/afficheevente", name="afficheevente")
     */
    public function afficheEvente(EvenementRepository $repository)
    {
        $event=$repository->findAll();
        $event=$repository->OrderByNom();
        return $this->render('back/evenement/afficheE.html.twig', ['event' => $event,]);
    }
    /**
     * @param $id
     * @param EvenementRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/supprimeevente/{id}" , name="supprimeevente")
     */

    function supprimerEvente($id , EvenementRepository $repository){
        $event=$repository->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($event);
        $em->flush();
        return $this->redirectToRoute('afficheevente');
    }

    /**
     * @Route("/listo69/backe", name="listo69", methods={"GET"})
     */
    public function listo69(TestRepository $testRepository): Response
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('back/test/listo1.html.twig', [
            'tests' => $testRepository->findAll(),
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (inline view)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => false
        ]);
    }


    /**
     * @Route("/test_index_back", name="test_index_back", methods={"GET"})
     */
    public function test_index_back(TestRepository $testRepository): Response
    {

        return $this->render('back/test/index.html.twig', [
            'tests' => $testRepository->findAll(),
        ]);
    }
    /**
     * @Route("/ajoutcatt",name="ajoutcatt")
     */

    public function ajoutCat(Request $request)
    {
        $categorie= new Cat();
        $form=$this->createForm(CatType::class,$categorie);
        $form= $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($categorie);
            $em->flush();
            return $this->redirectToRoute('affichecatt');
        }
        return $this->render('back/cat/ajoutC.html.twig',['form'=>$form->createView()]);
    }
    /**
     * @Route("/affichecatt", name="affichecatt")
     */
    public function afficheCat(CatRepository $repository)
    {
        $categorie=$repository->findAll();
        $categorie=$repository->OrderByNom();
        return $this->render('back/cat/afficheC.html.twig', ['cat' => $categorie,]);
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/supprimecatt/{id}" , name="supprimecatt")
     */

    function supprimerCat($id , CatRepository $repository){
        $categorie=$repository->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($categorie);
        $em->flush();
        return $this->redirectToRoute('affichecatt');
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/modifiecatt/{id}" , name="modifiecatt")
     */

    function modifierCat(CatRepository $repository ,$id , Request $request)

    {
        $categorie=$repository->find($id);
        $form=$this->createForm(CatType::class,$categorie);
        $form->add('Modifier',SubmitType::class);
        $form->handleRequest($request);
        if ( $form->isSubmitted() && $form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('affichecatt');
        }

        return $this->render('back/cat/modifieC.html.twig' , ['form'=>$form->createView()] );

    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/recherchecatt",name="recherchecatt")
     */

    function RechercheCat(CatRepository $repository , Request $request)
    {
        $nom=$request->get('recherche');
        $categorie=$repository->RechercheNom($nom);
        return $this->render('back/cat/afficheC.html.twig' , ['cat'=>$categorie]);
    }

    /**
     * @param EvenementRepository $repository
     * @param Request $request
     * @return Response
     * @Route("/rechercheeventt",name="rechercheeventt")
     */

    function RechercheEvent(EvenementRepository $repository , Request $request)
    {
        $nom=$request->get('recherche');
        $event=$repository->RechercheNom($nom);
        return $this->render('back/evenement/afficheE.html.twig' , ['event'=>$event]);
    }
    /**
     * @Route("/front", name="front", methods={"GET"})
     */
    public function index1(): Response
    {
        return $this->render('front/index.html.twig', [
            'controller_name' => 'BackController',
        ]);
    }
    /**
     *
     * @Route("/categoriesAll",name="categorieAll")
     */


    public function categorieAll(NormalizerInterface  $Normalizer)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository(Categorie::class);
        $categories = $repository->findAll();
        $jsonContent = $Normalizer->normalize($categories, 'json', ['groups' => 'post:read']);

        return new Response(json_encode($jsonContent));
    }
    /**
     * @Route("/categ", name="categ", methods={"GET"})
     */
    public function categ(CategorieRepository $categorieRepository ,NormalizerInterface $normalizer): Response
    {
        $cat = $categorieRepository->findAll();
        $jsonContent = $normalizer->normalize($cat,'json',['groups'=>'post:read']);
        return $this->render('back/categorie/index.html.twig', array(
            'categories' => $cat,
            'json_categories' => json_encode($jsonContent),
            ));
    }
    /**
     * @Route("/search1/{nom}", name="search1", methods={"GET","POST"})
     */
    public function search1(RecruteurRepository $recruteurRepository, $nom, Request $request): Response
    {
        $recruteur = new Recruteur();
        $searchForm = $this->createForm(\App\Form\Search1Type::class, $recruteur);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted()) {
            $nom = $searchForm['nom']->getData();
            $donnees = $recruteurRepository->search1($nom);
            return $this->redirectToRoute('search1', array('nom' => $nom));
        }
        $recruteur = $recruteurRepository->search1($nom);
        return $this->render('back/user.html.twig', [
            'recruteurs' => $recruteur,
            'searchForm' => $searchForm->createView()
        ]);
    }
    /**
     * @Route("/user", name="user", methods={"GET","POST"})
     */
    public function user(RecruteurRepository $recruteurRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $recruteur = new Recruteur();
        $searchForm = $this->createForm(\App\Form\Search1Type::class, $recruteur);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted()) {
            $nom = $searchForm['nom']->getData();
            $donnees = $recruteurRepository->search1($nom);
            return $this->redirectToRoute('search1', array('nom' => $nom));
        }
        $donnees = $this->getDoctrine()->getRepository(Recruteur::class)->findBy([], ['nom' => 'desc']);

        // Paginate the results of the query
        $offres = $paginator->paginate(
        // Doctrine Query, not results
            $donnees,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            4
        );
        return $this->render('back/user.html.twig', [
            'recruteurs' => $donnees,
            'searchForm' => $searchForm->createView()
        ]);
    }
    /**
     * @Route("/user_delete/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete2(Request $request, Recruteur $recruteur): Response
    {
        if ($this->isCsrfTokenValid('delete' . $recruteur->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($recruteur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user');
    }

    /**
     * @Route("/listo1", name="listo1", methods={"GET"})
     */
    public function listo1(RecruteurRepository $recruteurRepository): Response
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('back/listo1.html.twig', [
            'recruteurs' => $recruteurRepository->findAll(),
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (inline view)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => false
        ]);
    }

    /**
     * @Route("/search/{nom}", name="search", methods={"GET","POST"})
     */
    public function search(OffreRepository $offreRepository,$nom,Request $request,NormalizerInterface $normalizer): Response
    {
        $offre = new Offre();
        $searchForm = $this->createForm(\App\Form\SearchType::class,$offre);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted()) {
            $nom = $searchForm['nom']->getData();
            $donnees = $offreRepository->search($nom);
            return $this->redirectToRoute('search', array('nom' => $nom));
        }

        $offre = $offreRepository->search($nom);
        $jsonContent = $normalizer->normalize($offre,'json',['groups'=>'post:read']);
        return $this->render('back/offre_list.html.twig', [
            'offres' => $offre,
            'json_offres' => json_encode($jsonContent),
            'searchForm' => $searchForm->createView()
        ]);
    }
    /**
     * @Route("/offre_list", name="offre_list", methods={"GET","POST"})
     */
    public function offre(OffreRepository $offreRepository,Request $request, PaginatorInterface $paginator,NormalizerInterface $normalizer): Response
    {
        $offre = new Offre();
        $searchForm = $this->createForm(\App\Form\SearchType::class,$offre);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted()) {
            $nom = $searchForm['nom']->getData();
            $donnees = $offreRepository->search($nom);
            return $this->redirectToRoute('search', array('nom' => $nom));
        }
        $donnees = $this->getDoctrine()->getRepository(Offre::class)->findBy([],['abn' => 'desc']);

        // Paginate the results of the query
        $offres = $paginator->paginate(
        // Doctrine Query, not results
            $donnees,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            4
        );
        $jsonContent = $normalizer->normalize($donnees,'json',['groups'=>'post:read']);
        return $this->render('back/offre_list.html.twig', [
            'offres' => $offres,
            'json_offres' => json_encode($jsonContent),
            'searchForm' => $searchForm->createView()
        ]);
    }
    /**
     * @Route("/listfreel", name="listfreel", methods={"GET"})
     */
    public function listfreel(ProjetRepository $projetRepository): Response
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('back/projet/listfreel.html.twig', [
            'projet' => $projetRepository->findAll(),
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        $dompdf->set_option('isRemoteEnabled', true);
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

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

        // Output the generated PDF to Browser (inline view)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => false
        ]);
    }
    /**
     * @Route("/listo", name="listo", methods={"GET"})
     */
    public function listo(OffreRepository $offreRepository): Response
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('back/listo.html.twig', [
            'offres' => $offreRepository->findAll(),
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        $dompdf->set_option('isRemoteEnabled', true);
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

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

        // Output the generated PDF to Browser (inline view)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => false
        ]);
    }
    /**
     * @Route("/show/{id}", name="show", methods={"GET"})
     */
    public function show_offre(Offre $offre): Response
    {
        return $this->render('back/offre_show.html.twig', [
            'offre' => $offre,
        ]);
    }
    /**
     * @Route("/offre_delete/{id}", name="offre_delete", methods={"DELETE"})
     */
    public function delete1(Request $request, Offre $offre): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offre->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($offre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('offre_list');
    }
    /**
     * @Route ("/recruFiltre/{type}",name="filtre")
     * @param NormalizerInterface $Normalizer
     * @param $type
     */
    public function filtreTransport(NormalizerInterface $Normalizer, $type)
    {
        $repository = $this->getDoctrine()->getRepository(Recruteur::class);
        $recruteur = $repository->findTypeRecruteur($type);
        $jsonContent = $Normalizer->normalize($recruteur, 'json', ['groups' => 'recruteur']);
        //var_dump($users);
        return new Response(json_encode($jsonContent));

    }
    /**
     * @Route("admin/stat", name="statuser")
     */
    public function statuser(){
        $em = $this->getDoctrine()->getManager();
        $conn = $em->getConnection();

        $sqlAdmin2 = 'SELECT type , COUNT(*) AS toBeUsed FROM recruteur  GROUP BY type';
        $stmtAdmin2 = $conn->prepare($sqlAdmin2);


        $stmtAdmin2->execute();
        $arrayAdmin2 = $stmtAdmin2->fetchAll();


        $data2 = array(['type','Nombre de user']);
        foreach ($arrayAdmin2 as $item){
            array_push($data2,[$item['type'],intval($item['toBeUsed'])]);
        }

        $pieChart = new PieChart();
        $pieChart->getData()->setArrayToDataTable($data2);
        $pieChart->getOptions()->setTitle('Pourcentages des user pour chaque type');
        $pieChart->getOptions()->setWidth(600);
        $pieChart->getOptions()->setHeight(400);

        return $this->render('back/statrec.html.twig', array(

            'arrayAdmin2' => $arrayAdmin2,
            'piechart'=>$pieChart ,
        ));

    }
    /**
     * @Route("stat", name="stat")
     */
    public function stat(){
        $em = $this->getDoctrine()->getManager();
        $conn = $em->getConnection();

        $sqlAdmin2 = 'SELECT job_salary , COUNT(*) AS toBeUsed FROM projet  GROUP BY job_salary';
        $stmtAdmin2 = $conn->prepare($sqlAdmin2);


        $stmtAdmin2->execute();
        $arrayAdmin2 = $stmtAdmin2->fetchAll();

        $data2 = array(['salaire','gestion salaires']);
        foreach ($arrayAdmin2 as $item){
            array_push($data2,[$item['job_salary'],intval($item['toBeUsed'])]);
        }

        $pieChart = new PieChart();
        $pieChart->getData()->setArrayToDataTable($data2);
        $pieChart->getOptions()->setTitle('Pourcentages des reclamations pour chaque Object');
        $pieChart->getOptions()->setWidth(600);
        $pieChart->getOptions()->setHeight(400);

        return $this->render('back/projet/satatrec.html.twig', array(

            'arrayAdmin2' => $arrayAdmin2,
            'piechart'=>$pieChart ,

        ));

    }
    /**
     * @Route("/{id}", name="projet_delete1", methods={"DELETE"})
     */
    public function projet_delete(Request $request, Projet $projet): Response
    {
        if ($this->isCsrfTokenValid('delete'.$projet->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($projet);
            $entityManager->flush();
        }


        return $this->redirectToRoute('projetBack');

    }
    /**
     * @Route("/test/new", name="test_new", methods={"GET","POST"})
     */
    public function test_new(Request $request): Response
    {
        $test = new Test();
        $form = $this->createForm(TestType::class, $test);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($test);
            $entityManager->flush();

            return $this->redirectToRoute('test_index_back');
        }

        return $this->render('back/test/new.html.twig', [
            'test' => $test,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("test_show/{id}", name="test_show", methods={"GET"})
     */
    public function test_show(Test $test): Response
    {
        return $this->render('back/test/show.html.twig', [
            'test' => $test,
        ]);
    }

    /**
     * @Route("test_edit/{id}/edit", name="test_edit", methods={"GET","POST"})
     */
    public function test_edit(Request $request, Test $test): Response
    {
        $form = $this->createForm(TestType::class, $test);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('test_index_back');
        }

        return $this->render('back/test/edit.html.twig', [
            'test' => $test,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("test_delete/del/{id}", name="test_delete", methods={"DELETE"})
     */
    public function test_delete(Request $request, Test $test): Response
    {
        if ($this->isCsrfTokenValid('delete'.$test->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($test);
            $entityManager->flush();
        }

        return $this->redirectToRoute('test_index_back');
    }

    /**
     * @Route("/certificat_index_back", name="certificat_index_back", methods={"GET"})
     */
    public function certificat_index(CertificatRepository $certificatRepository): Response
    {
        return $this->render('back/certificat/index.html.twig', [
            'certificats' => $certificatRepository->findAll(),
        ]);
    }

    /**
     * @Route("certificat_new/new", name="certificat_new", methods={"GET","POST"})
     */
    public function certificat_new(Request $request): Response
    {
        $certificat = new Certificat();
        $form = $this->createForm(CertificatType::class, $certificat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($certificat);
            $entityManager->flush();

            return $this->redirectToRoute('certificat_index_back');
        }

        return $this->render('back/certificat/new.html.twig', [
            'certificat' => $certificat,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/affiche_recla", name="affiche_recla", methods={"GET"})
     */
    public function affiche_recla(ReclamationRepository $reclamationRepository): Response
    {
        return $this->render('back/reclamation/affiche_recla.html.twig', [
            'reclamations' => $reclamationRepository->findAll(),
        ]);
    }
    /**
     * @Route("/recla_delete/{id}", name="recla_delete", methods={"DELETE"})
     */
    public function recla_delete(Request $request, Reclamation $reclamation): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('affiche_recla');
    }
    /**
     * @Route("certificat_show/back/{id}", name="certificat_show", methods={"GET"})
     */
    public function certificat_show(Certificat $certificat): Response
    {
        return $this->render('back/certificat/show.html.twig', [
            'certificat' => $certificat,
        ]);
    }

    /**
     * @Route("certificat_edit/{id}/edit", name="certificat_edit", methods={"GET","POST"})
     */
    public function certificat_edit(Request $request, Certificat $certificat): Response
    {
        $form = $this->createForm(CertificatType::class, $certificat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('certificat_index_back');
        }

        return $this->render('back/certificat/edit.html.twig', [
            'certificat' => $certificat,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("certificat_delete/{id}", name="certificat_delete", methods={"DELETE"})
     */
    public function certificat_delete(Request $request, Certificat $certificat): Response
    {
        if ($this->isCsrfTokenValid('delete'.$certificat->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($certificat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('certificat_index_back');
    }
    /**
     * @Route("/projetBack", name="projetBack", methods={"GET"})
     */

    public function projetBack(ProjetRepository $repository){
        $projet=$repository->findAll();
        return $this->render('back/projet/projetBack.html.twig',[
            "projet"=>$projet
        ]);
    }
    /**
     * @Route("/delete/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(Request $request, Categorie $categorie): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($categorie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('categ');
    }
    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     */
    public function new(Request $request,NormalizerInterface $normalizer): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['photo']->getData();
            $filename = md5(uniqid()).'.'.$uploadedFile->guessExtension();
            $uploadedFile->move($this->getParameter('upload_directory'),$filename);
            $categorie->setPhoto($filename);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($categorie);
            $entityManager->flush();
            return $this->redirectToRoute('categ');
        }
        $jsonContent = $normalizer->normalize($categorie,'json',['groups'=>'post:read']);
        return $this->render('back/categorie/new.html.twig', [
            'categorie' => $categorie,
             'json_categorie'=> json_encode($jsonContent),
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("{id}", name="show1", methods={"GET"})
     */
    public function show(Categorie $categorie): Response
    {
        return $this->render('back/categorie/edit.html.twig', [
            'categorie' => $categorie,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Categorie $categorie,NormalizerInterface $normalizer): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['photo']->getData();
            $filename = md5(uniqid()).'.'.$uploadedFile->guessExtension();
            $uploadedFile->move($this->getParameter('upload_directory'),$filename);
            $categorie->setPhoto($filename);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('categ');
        }
        $jsonContent = $normalizer->normalize($categorie,'json',['groups'=>'post:read']);
        return $this->render('back/categorie/edit.html.twig', [
            'categorie' => $categorie,
             'json_categorie'=>json_encode($jsonContent),
            'form' => $form->createView(),
        ]);


    }
    /**
     * @param ForumRepository $repository
     * @return Response
     * @Route ("/AfficheForumAd",name="AfficheForumAd")
     */
    public function showForumAdmin(ForumRepository $repository){
        $forum=$repository->findBy([],['date' => 'ASC']);
        return $this->render('back/forum/index.html.twig', ['forum' => $forum]);
    }

    /**
     * @param $id
     * @param ForumRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("back/supp/{id}" , name="de")
     */

    public function deleteForumAd($id,ForumRepository $repository){
        $forum=$repository->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($forum);
        $em->flush();
        return$this->redirectToRoute('AfficheForumAd');
    }

    /**
     *
     *  @param Request $request
     *  @param Forum $forum
     * @Route("/forumAd/{id}/show", name="forum_showAd")
     */
    public function showAd( Request $request, Forum $forum,CommenterRepository $repository)
    {
        // $comm->setForum($forum);
        $comm = $repository->findAll();
        return $this->render('back/forum/show.html.twig', [
            'id' => $forum->getId(),
            'forum' => $forum,
            'comm' => $comm

        ]);
    }

    /**
     * @param $ref
     * @param CommenterRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route ("/suppCommAd/{ref}" , name="dca")
     */
    public function deleteCommAd($ref,CommenterRepository $repository){
        $comm=$repository->find($ref);
        $id=$comm->getForum()->getId();
        $em=$this->getDoctrine()->getManager();
        $em->remove($comm);
        $em->flush();
        return$this->redirectToRoute('forum_showAd',array('id'=>$id));
    }
    /**
     * @param ForumRepository $annRepo
     * @return Response
     * @Route ("/stats", name="stats")
     */

    public function statistiques(ForumRepository $annRepo){
        $forum = $annRepo->countByDate();
        $dates = [];
        $annoncesCount = [];
        foreach($forum as $foru){

            $dates [] = $foru['date'];
            $annoncesCount[] = $foru['count'];
        }
        return $this->render('back/forum/stats.html.twig', [
            'dates' => $dates,
            'annoncesCount' => $annoncesCount
        ]);
    }
    /**
     * @param ForumRepository $repository
     * @param Request $request
     * @return Response
     * @Route ("forumBack/searchs", name="rechercheForumBack")
     */

    function SearchS (ForumRepository $repository,Request $request) {
        $sujet=$request->get('search');
        $forum=$repository->searchS($sujet);
        return $this->render('back/forum/index.html.twig', ['forum'=>$forum]);
    }
}
