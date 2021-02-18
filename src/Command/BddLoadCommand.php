<?php

namespace App\Command;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BddLoadCommand extends Command
{
    protected static $defaultName = 'app:bdd:load';
    protected static $defaultDescription = 'Add a short description for your command';

    protected $entityManager;
    protected $encoder;
    protected $serializer;
    protected $httpClient;
    protected $villeRepository;
    protected $lieuRepository;

    public function __construct(string $name = null,EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder,
                                SerializerInterface $serializer, HttpClientInterface $httpClient,
                                VilleRepository $villeRepository,LieuRepository $lieuRepository)
    {
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
        $this->serializer =$serializer;
        $this->httpClient = $httpClient;
        $this->villeRepository = $villeRepository;
        $this->lieuRepository = $lieuRepository;


        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $manager = $this->entityManager;

        $connection = $this->entityManager->getConnection();
        $connection->executeQuery("SET FOREIGN_KEY_CHECKS = 0");
        $connection->executeQuery("TRUNCATE TABLE sortie");
        $connection->executeQuery("TRUNCATE TABLE lieu");
        $connection->executeQuery("TRUNCATE TABLE ville");
        $connection->executeQuery("TRUNCATE TABLE etat");
        $connection->executeQuery("TRUNCATE TABLE campus");
        $connection->executeQuery("TRUNCATE TABLE participant");
        $connection->executeQuery("TRUNCATE TABLE participant_sortie");
        $connection->executeQuery("SET FOREIGN_KEY_CHECKS = 1");

        //Mise en BDD des états possible d'une sorties
        $etat1 = new Etat();
        $etat1->setLibelle("Créée");
        $manager->persist($etat1);
        $etat2 = new Etat();
        $etat2->setLibelle("Ouverte");
        $manager->persist($etat2);
        $etat3 = new Etat();
        $etat3->setLibelle("Clôturée");
        $manager->persist($etat3);
        $etat4 = new Etat();
        $etat4->setLibelle("Activité en cours");
        $manager->persist($etat4);
        $etat5 = new Etat();
        $etat5->setLibelle("Passée");
        $manager->persist($etat5);
        $etat6 = new Etat();
        $etat6->setLibelle("Annulée");
        $manager->persist($etat6);
        $etat7 = new Etat();
        $etat7->setLibelle("Archivée");
        $manager->persist($etat7);

        //Mise en BDD des différents campus
        $campus1 = new Campus();
        $campus1->setNom("Campus Nantes Faraday");
        $manager->persist($campus1);
        $campus2 = new Campus();
        $campus2->setNom("Campus de Rennes");
        $manager->persist($campus2);
        $campus3 = new Campus();
        $campus3->setNom("Campus de Quimper");
        $manager->persist($campus3);
        $campus4 = new Campus();
        $campus4->setNom("Campus de Niort");
        $manager->persist($campus4);
        $campus5 = new Campus();
        $campus5->setNom("Formations à La Roche-sur-Yon");
        $manager->persist($campus5);
        $campus6 = new Campus();
        $campus6->setNom("Formations à Angers");
        $manager->persist($campus6);
        $campus7 = new Campus();
        $campus7->setNom("Formations au Mans");
        $manager->persist($campus7);
        $campus8 = new Campus();
        $campus8->setNom("Formations à Laval");
        $manager->persist($campus8);

        $participantUser = new Participant();
        $participantUser->setPseudo("user")
            ->setNom("UserNom")
            ->setPrenom("UserPrenom")
            ->setTelephone("0123456789")
            ->setMail("user@user.com")
            ->setPassword($this->encoder->encodePassword($participantUser,"user"))
            ->setRoles(["ROLE_USER"])
            ->setActif(true)
            ->setCampus($campus1);
        $manager->persist($participantUser);

        $participantAdmin = new Participant();
        $participantAdmin->setPseudo("admin")
            ->setNom("AdminNom")
            ->setPrenom("AdminPrenom")
            ->setTelephone("0223456789")
            ->setMail("admin@admin.com")
            ->setPassword($this->encoder->encodePassword($participantAdmin,"admin"))
            ->setRoles(["ROLE_ADMIN"])
            ->setActif(true)
            ->setCampus($campus2);
        $manager->persist($participantAdmin);

        $participantSuperAdmin = new Participant();
        $participantSuperAdmin->setPseudo("superadmin")
            ->setNom("SuperAdminNom")
            ->setPrenom("SuperAdminPrenom")
            ->setTelephone("0323456789")
            ->setMail("superadmin@superadmin.com")
            ->setPassword($this->encoder->encodePassword($participantSuperAdmin,"superadmin"))
            ->setRoles(["ROLE_SUPER_ADMIN"])
            ->setActif(true)
            ->setCampus($campus3);
        $manager->persist($participantSuperAdmin);

        $amidenSuperAdmin = new Participant();
        $amidenSuperAdmin->setPseudo("Amiden")
            ->setNom("Puaud")
            ->setPrenom("Damien")
            ->setTelephone("0689626729")
            ->setMail("damien.puaud22020@campus-eni.fr")
            ->setPassword($this->encoder->encodePassword($amidenSuperAdmin,"amidenadmin"))
            ->setRoles(["ROLE_SUPER_ADMIN"])
            ->setActif(true)
            ->setCampus($campus1);
        $manager->persist($amidenSuperAdmin);

        $borisSuperAdmin = new Participant();
        $borisSuperAdmin->setPseudo("Boris")
            ->setNom("Oger")
            ->setPrenom("Boris")
            ->setTelephone("0323456789")
            ->setMail("boris.oger2020@campus-eni.fr")
            ->setPassword($this->encoder->encodePassword($borisSuperAdmin,"pa\$\$word"))
            ->setRoles(["ROLE_SUPER_ADMIN"])
            ->setActif(true)
            ->setCampus($campus1);
        $manager->persist($borisSuperAdmin);

        $arkoSuperAdmin = new Participant();
        $arkoSuperAdmin->setPseudo("Arko")
            ->setNom("Leclere")
            ->setPrenom("François")
            ->setTelephone("0323456790")
            ->setMail("francois.leclere2020@campus-eni.fr")
            ->setPassword($this->encoder->encodePassword($arkoSuperAdmin,"arkoadmin"))
            ->setRoles(["ROLE_SUPER_ADMIN"])
            ->setActif(true)
            ->setCampus($campus1);
        $manager->persist($arkoSuperAdmin);

        $florianSuperAdmin = new Participant();
        $florianSuperAdmin->setPseudo("Murazakiiro")
            ->setNom("SINAMA")
            ->setPrenom("Florian")
            ->setTelephone("0123456789")
            ->setMail("florian.sinama2020@campus-eni.fr")
            ->setPassword($this->encoder->encodePassword($florianSuperAdmin,"florianadmin"))
            ->setRoles(["ROLE_SUPER_ADMIN"])
            ->setActif(true)
            ->setCampus($campus1);
        $manager->persist($florianSuperAdmin);

        $manager->flush();

        //Mise en BDD des Ville du Poitou Charente
        $villes = $this->httpClient->request(
            'GET',
            "https://data.enseignementsup-recherche.gouv.fr/api/records/1.0/search/?dataset=fr-esr-referentiel-geographique&q=&rows=10000&facet=regrgp_nom&facet=reg_nom&facet=reg_nom_old&facet=aca_nom&facet=dep_nom&facet=com_code&facet=uucr_nom&refine.reg_nom=Nouvelle-Aquitaine&refine.reg_nom_old=Poitou-Charentes"
        )->toArray();


        for ($i = 0; $i < count($villes["records"]); $i += 1){
            $villeAjouter = new Ville();
            $villeAjouter->setNom($villes["records"][$i]["fields"]["com_nom"])
                ->setCodePostal($villes["records"][$i]["fields"]["com_code"])
            ;
            $manager->persist($villeAjouter);
        }
        $manager->flush();

        //Mise en BDD des Ville du Pays de la loire
        $villes = $this->httpClient->request(
            'GET',
            "https://data.enseignementsup-recherche.gouv.fr/api/records/1.0/search/?dataset=fr-esr-referentiel-geographique&q=&rows=10000&facet=regrgp_nom&facet=reg_nom&facet=reg_nom_old&facet=aca_nom&facet=dep_nom&facet=com_code&facet=uucr_nom&refine.reg_nom=Pays+de+la+Loire"
        )->toArray();


        for ($i = 0; $i < count($villes["records"]); $i += 1){
            $villeAjouter = new Ville();
            $villeAjouter->setNom($villes["records"][$i]["fields"]["com_nom"])
                ->setCodePostal($villes["records"][$i]["fields"]["com_code"])
            ;
            $manager->persist($villeAjouter);
        }
        $manager->flush();

        //Mise en BDD des Ville de la Bretagne
        $villes = $this->httpClient->request(
            'GET',
            "https://data.enseignementsup-recherche.gouv.fr/api/records/1.0/search/?dataset=fr-esr-referentiel-geographique&q=&rows=10000&facet=regrgp_nom&facet=reg_nom&facet=reg_nom_old&facet=aca_nom&facet=dep_nom&facet=com_code&facet=uucr_nom&refine.reg_nom=Bretagne"
        )->toArray();


        for ($i = 0; $i < count($villes["records"]); $i += 1){
            $villeAjouter = new Ville();
            $villeAjouter->setNom($villes["records"][$i]["fields"]["com_nom"])
                ->setCodePostal($villes["records"][$i]["fields"]["com_code"])
            ;
            $manager->persist($villeAjouter);
        }
        $manager->flush();


        //Mise en Bdd des lieu(Cinéma de france)
        $cinemas = $this->httpClient->request(
            'GET',
            "https://data.iledefrance.fr/api/records/1.0/search/?dataset=les_salles_de_cinemas_en_ile-de-france&q=&facet=dep&facet=tranche_d_entrees&facet=ae&facet=multiplexe"
        )->toArray();


        foreach ($cinemas["records"] as $cinema){
            $cine = new Lieu();
            $cine->setNom($cinema["fields"]["nom"])
                ->setLatitude($cinema["fields"]["geo"][0])
                ->setLongitude($cinema["fields"]["geo"][1])
                ->setRue($cinema["fields"]["adresse"])
                ->setVille(
                    $this->villeRepository->findOneBy(['id'=>rand(1,4462)])
                );
            ;
            $manager->persist($cine);
        }

        $manager->flush();

        //Jeu Sortie 1 BDD
        $dateDebutTest1 = new \DateTime();
        $dateDebutTest1->add(new \DateInterval('P0Y0M14DT0H0M0S'));
        $dateDebutSortieTest1 = new \DateTime($dateDebutTest1->format('Y-M-d H:i:s'));
        $dateInsTest1 = new \DateTime();
        $dateInsTest1->add(new \DateInterval('P0Y0M13DT0H0M0S'));
        $dateInsSortieTest1 = new \DateTime($dateInsTest1->format('Y-M-d H:i:s'));

        $sortieTest1 = new Sortie();
        $sortieTest1->setNom("Observation des Artichauds Jupitériens")
            ->setDateHeureDebut($dateDebutSortieTest1)
            ->setDuree(240)
            ->setDateLimiteInscription($dateInsSortieTest1)
            ->setNbInscriptionsMax(18)
            ->setInfosSortie("Ce weekend est la saison de reproduction des artichauds de Jupiter : un événement observable seulement tous les 452 ans. Je vous invite à prendre vos lunettes astronomique pour observer ça ensemble")
            ->setEtat($etat2)
            ->setOrganisateur($amidenSuperAdmin)
            ->addParticipant($amidenSuperAdmin)
            ->setLieu($this->lieuRepository->findOneBy(['id'=>rand(1,10)]))
            ->setCampus($campus2);
        $manager->persist($sortieTest1);

        //Jeu Sortie 2 BDD
        $dateDebutTest2 = new \DateTime();
        $dateDebutTest2->add(new \DateInterval('P0Y0M10DT0H0M0S'));
        $dateDebutSortieTest2 = new \DateTime($dateDebutTest2->format('Y-M-d H:i:s'));
        $dateInsTest2 = new \DateTime();
        $dateInsTest2->add(new \DateInterval('P0Y0M9DT0H0M0S'));
        $dateInsSortieTest2 = new \DateTime($dateInsTest2->format('Y-M-d H:i:s'));

        $sortieTest2 = new Sortie();
        $sortieTest2->setNom("Combat de coqs au Vietnam")
            ->setDateHeureDebut($dateDebutSortieTest2)
            ->setDuree(10080)
            ->setDateLimiteInscription($dateInsSortieTest2)
            ->setNbInscriptionsMax(12)
            ->setInfosSortie("Je vous invite à participer à un voyage au Vietnam pour aller parier illégalement sur un combat de coq. Favori : Poullay O'Curry")
            ->setEtat($etat2)
            ->setOrganisateur($arkoSuperAdmin)
            ->addParticipant($arkoSuperAdmin)
            ->setLieu($this->lieuRepository->findOneBy(['id'=>rand(1,10)]))
            ->setCampus($campus2);
        $manager->persist($sortieTest2);

        //Jeu Sortie 3 BDD
        $dateDebutTest3 = new \DateTime();
        $dateDebutTest3->add(new \DateInterval('P0Y0M9DT0H0M0S'));
        $dateDebutSortieTest3 = new \DateTime($dateDebutTest3->format('Y-M-d H:i:s'));
        $dateInsTest3 = new \DateTime();
        $dateInsTest3->add(new \DateInterval('P0Y0M8DT0H0M0S'));
        $dateInsSortieTest3 = new \DateTime($dateInsTest3->format('Y-M-d H:i:s'));

        $sortieTest3 = new Sortie();
        $sortieTest3->setNom("Jouer à la marelle sur les falaises d'Etretat")
            ->setDateHeureDebut($dateDebutSortieTest3)
            ->setDuree(120)
            ->setDateLimiteInscription($dateInsSortieTest3)
            ->setNbInscriptionsMax(20)
            ->setInfosSortie("Je vous propose une activité ludique qui vous fera retourner en enfance : jouer à la marelle. Petite difficulté supplémentaire : nous jouerons les yeux bandés au bord des falaises d'Etretat. Je suis en train de négocier un partenariat de sponsor avec Red Bull")
            ->setEtat($etat2)
            ->setOrganisateur($borisSuperAdmin)
            ->addParticipant($borisSuperAdmin)
            ->setLieu($this->lieuRepository->findOneBy(['id'=>rand(1,10)]))
            ->setCampus($campus2);
        $manager->persist($sortieTest3);

        //Jeu Sortie 4 BDD
        $dateDebutTest4 = new \DateTime();
        $dateDebutTest4->add(new \DateInterval('P0Y0M8DT0H0M0S'));
        $dateDebutSortieTest4 = new \DateTime($dateDebutTest4->format('Y-M-d H:i:s'));
        $dateInsTest4 = new \DateTime();
        $dateInsTest4->add(new \DateInterval('P0Y0M7DT0H0M0S'));
        $dateInsSortieTest4 = new \DateTime($dateInsTest4->format('Y-M-d H:i:s'));

        $sortieTest4 = new Sortie();
        $sortieTest4->setNom("Partie de Quidditch moldu")
            ->setDateHeureDebut($dateDebutSortieTest4)
            ->setDuree(180)
            ->setDateLimiteInscription($dateInsSortieTest4)
            ->setNbInscriptionsMax(14)
            ->setInfosSortie("Également connu sous le nom de \"quidditch au sol\", le quidditch moldu (\"muggle quidditch\" en anglais) est issu de la saga littéraire Harry Potter. Il oppose deux équipes de sept joueurs dotés de balais.")
            ->setEtat($etat2)
            ->setOrganisateur($florianSuperAdmin)
            ->addParticipant($florianSuperAdmin)
            ->setLieu($this->lieuRepository->findOneBy(['id'=>rand(1,10)]))
            ->setCampus($campus2);
        $manager->persist($sortieTest4);

        //Jeu Sortie 5 BDD
        $dateDebutTest5 = new \DateTime();
        $dateDebutTest5->add(new \DateInterval('P0Y0M4DT0H0M0S'));
        $dateDebutSortieTest5 = new \DateTime($dateDebutTest5->format('Y-M-d H:i:s'));
        $dateInsTest5 = new \DateTime();
        $dateInsTest5->add(new \DateInterval('P0Y0M3DT0H0M0S'));
        $dateInsSortieTest5 = new \DateTime($dateInsTest5->format('Y-M-d H:i:s'));

        $sortieTest5 = new Sortie();
        $sortieTest5->setNom("Dompter des mouches")
            ->setDateHeureDebut($dateDebutSortieTest5)
            ->setDuree(90)
            ->setDateLimiteInscription($dateInsSortieTest5)
            ->setNbInscriptionsMax(6)
            ->setInfosSortie("Petit atelier pour apprendre à dompter des mouches pour leur faire faire des enchaînements de cirque comme passer dans des anneaux de feu ou des acrobaties au trapèze")
            ->setEtat($etat2)
            ->setOrganisateur($amidenSuperAdmin)
            ->addParticipant($amidenSuperAdmin)
            ->setLieu($this->lieuRepository->findOneBy(['id'=>rand(1,10)]))
            ->setCampus($campus2);
        $manager->persist($sortieTest5);

        //Jeu Sortie 6 BDD
        $dateDebutTest6 = new \DateTime();
        $dateDebutTest6->add(new \DateInterval('P0Y0M2DT0H0M0S'));
        $dateDebutSortieTest6 = new \DateTime($dateDebutTest6->format('Y-M-d H:i:s'));
        $dateInsTest6 = new \DateTime();
        $dateInsTest6->add(new \DateInterval('P0Y0M1DT0H0M0S'));
        $dateInsSortieTest6 = new \DateTime($dateInsTest6->format('Y-M-d H:i:s'));

        $sortieTest6 = new Sortie();
        $sortieTest6->setNom("Marathon Joséphine Ange Gardien")
            ->setDateHeureDebut($dateDebutSortieTest6)
            ->setDuree(20160)
            ->setDateLimiteInscription($dateInsSortieTest6)
            ->setNbInscriptionsMax(80)
            ->setInfosSortie("On organise un binge watching de Joséphine!! 20 saisons en une fois ! Prévoyez 2 semaines, des pop corn et une sonde urinaire. A bientôt !")
            ->setEtat($etat2)
            ->setOrganisateur($arkoSuperAdmin)
            ->addParticipant($arkoSuperAdmin)
            ->setLieu($this->lieuRepository->findOneBy(['id'=>rand(1,10)]))
            ->setCampus($campus2);
        $manager->persist($sortieTest6);

        //Jeu Sortie 7 BDD
        $dateDebutTest7 = new \DateTime();
        $dateDebutTest7->add(new \DateInterval('P0Y0M2DT0H0M0S'));
        $dateDebutSortieTest7 = new \DateTime($dateDebutTest7->format('Y-M-d H:i:s'));
        $dateInsTest7 = new \DateTime();
        $dateInsTest7->add(new \DateInterval('P0Y0M1DT0H0M0S'));
        $dateInsSortieTest7 = new \DateTime($dateInsTest7->format('Y-M-d H:i:s'));

        $sortieTest7 = new Sortie();
        $sortieTest7->setNom("La Bible en pratique : Transformer l'eau en vin")
            ->setDateHeureDebut($dateDebutSortieTest7)
            ->setDuree(130)
            ->setDateLimiteInscription($dateInsSortieTest7)
            ->setNbInscriptionsMax(32)
            ->setInfosSortie("Je vous propose de mettre en pratique mon passage préféré de la Bible : transformer de l'eau en vin. Merci baby Jesus! (Évangile selon Jean, 2,1-11)")
            ->setEtat($etat2)
            ->setOrganisateur($borisSuperAdmin)
            ->addParticipant($borisSuperAdmin)
            ->setLieu($this->lieuRepository->findOneBy(['id'=>rand(1,10)]))
            ->setCampus($campus2);
        $manager->persist($sortieTest7);

        //Jeu Sortie 8 BDD
        $dateDebutTest8 = new \DateTime();
        $dateDebutTest8->add(new \DateInterval('P0Y0M5DT0H0M0S'));
        $dateDebutSortieTest8 = new \DateTime($dateDebutTest8->format('Y-M-d H:i:s'));
        $dateInsTest8 = new \DateTime();
        $dateInsTest8->add(new \DateInterval('P0Y0M3DT0H0M0S'));
        $dateInsSortieTest8 = new \DateTime($dateInsTest8->format('Y-M-d H:i:s'));

        $sortieTest8 = new Sortie();
        $sortieTest8->setNom("Death Star en Babybel")
            ->setDateHeureDebut($dateDebutSortieTest8)
            ->setDuree(2880)
            ->setDateLimiteInscription($dateInsSortieTest8)
            ->setNbInscriptionsMax(14)
            ->setInfosSortie("Ce weekend nous allons construire l'Étoile de la mort grandeur nature en cire de coques rouges de Babybel! Venez avec toute votre famille")
            ->setEtat($etat2)
            ->setOrganisateur($florianSuperAdmin)
            ->addParticipant($florianSuperAdmin)
            ->setLieu($this->lieuRepository->findOneBy(['id'=>rand(1,10)]))
            ->setCampus($campus2);
        $manager->persist($sortieTest8);


        //Sortie test de passage de l'état 2 vers l'etat 3
        $dateDebut1 = new \DateTime();
        $dateDebut1->add(new \DateInterval('P0Y0M7DT0H0M0S'));
        $dateDebutSortie1 = new \DateTime($dateDebut1->format('Y-M-d H:i:s'));
        $dateIns1 = new \DateTime();
        $dateIns1->sub(new \DateInterval('P0Y0M1DT0H0M0S'));
        $dateInsSortie1 = new \DateTime($dateIns1->format('Y-M-d H:i:s'));

        $sortie1 = new Sortie();
        $sortie1->setNom("Test etat 'Ouverte' vers 'Clôturée'")
            ->setDateHeureDebut($dateDebutSortie1)
            ->setDuree(30)
            ->setDateLimiteInscription($dateInsSortie1)
            ->setNbInscriptionsMax(20)
            ->setInfosSortie("Blablabla test")
            ->setEtat($etat2)
            ->setOrganisateur($amidenSuperAdmin)
            ->addParticipant($amidenSuperAdmin)
            ->setLieu($this->lieuRepository->findOneBy(['id'=>rand(1,10)]))
            ->setCampus($campus2);
        $manager->persist($sortie1);

        //Sortie test de passage de l'état 3 vers l'etat 4
        $datsortie2 = new \DateTime();
        $datsortie2->sub(new \DateInterval('P0Y0M0DT1H0M0S'));
        $dateDebutsortie2 = new \DateTime($datsortie2->format('Y-M-d H:i:s'));
        $dsortie2 = new \DateTime();
        $dsortie2->sub(new \DateInterval('P0Y0M1DT0H0M0S'));
        $dateInssortie2 = new \DateTime($dsortie2->format('Y-M-d H:i:s'));

        $sortie2 = new Sortie();
        $sortie2->setNom("Test etat 'Clôturée' vers 'Activité en cours'")
            ->setDateHeureDebut($dateDebutsortie2)
            ->setDuree(240)
            ->setDateLimiteInscription($dateInssortie2)
            ->setNbInscriptionsMax(20)
            ->setInfosSortie("Blablabla test")
            ->setEtat($etat3)
            ->setOrganisateur($amidenSuperAdmin)
            ->addParticipant($amidenSuperAdmin)
            ->setLieu($this->lieuRepository->findOneBy(['id'=>rand(1,10)]))
            ->setCampus($campus2);
        $manager->persist($sortie2);

        //Sortie test de passage de l'état 4 vers l'etat 5
        $datsortie3 = new \DateTime();
        $datsortie3->sub(new \DateInterval('P0Y0M0DT4H0M0S'));
        $dateDebutsortie3 = new \DateTime($datsortie3->format('Y-M-d H:i:s'));
        $dsortie3 = new \DateTime();
        $dsortie3->sub(new \DateInterval('P0Y0M1DT0H0M0S'));
        $dateInssortie3 = new \DateTime($dsortie3->format('Y-M-d H:i:s'));

        $sortie3 = new Sortie();
        $sortie3->setNom("Test etat 'Activité en cours' vers 'Passée'")
            ->setDateHeureDebut($dateDebutsortie3)
            ->setDuree(30)
            ->setDateLimiteInscription($dateInssortie3)
            ->setNbInscriptionsMax(20)
            ->setInfosSortie("Blablabla test")
            ->setEtat($etat4)
            ->setOrganisateur($amidenSuperAdmin)
            ->addParticipant($amidenSuperAdmin)
            ->setLieu($this->lieuRepository->findOneBy(['id'=>rand(1,10)]))
            ->setCampus($campus2);
        $manager->persist($sortie3);

        //Sortie test de passage de l'état 5 vers l'etat 7
        $datsortie4 = new \DateTime();
        $datsortie4->sub(new \DateInterval('P0Y1M1DT0H0M0S'));
        $dateDebutsortie4 = new \DateTime($datsortie4->format('Y-M-d H:i:s'));
        $dsortie4 = new \DateTime();
        $dsortie4->sub(new \DateInterval('P0Y1M2DT0H0M0S'));
        $dateInssortie4 = new \DateTime($dsortie4->format('Y-M-d H:i:s'));

        $sortie4 = new Sortie();
        $sortie4->setNom("Test etat 'Passée' vers 'Archivée'")
            ->setDateHeureDebut($dateDebutsortie4)
            ->setDuree(30)
            ->setDateLimiteInscription($dateInssortie4)
            ->setNbInscriptionsMax(20)
            ->setInfosSortie("Blablabla test")
            ->setEtat($etat5)
            ->setOrganisateur($amidenSuperAdmin)
            ->addParticipant($amidenSuperAdmin)
            ->setLieu($this->lieuRepository->findOneBy(['id'=>rand(1,10)]))
            ->setCampus($campus2);
        $manager->persist($sortie4);

        $manager->flush();
        $connection->executeQuery("DROP EVENT IF EXISTS `Gestion_Etat`");
        $connection->executeQuery("CREATE DEFINER=`root`@`localhost` EVENT `Gestion_Etat` ON SCHEDULE EVERY 1 DAY STARTS '2021-02-15 00:30:00' ENDS '2028-02-15 00:35:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
                                    UPDATE sortie as s SET s.etat_id = 3 
                                    WHERE s.date_limite_inscription <= NOW();
                                    
                                    UPDATE sortie as s SET s.etat_id = 4 
                                    WHERE s.date_heure_debut <= NOW();
                                    
                                    UPDATE sortie as s SET s.etat_id = 5 
                                    WHERE DATE_ADD(s.date_heure_debut, INTERVAL s.duree MINUTE) <= NOW();
                                    
                                    UPDATE sortie as s SET s.etat_id = 7 
                                    WHERE DATE_ADD(DATE_ADD(s.date_heure_debut, INTERVAL s.duree MINUTE), INTERVAL '31' DAY) <= NOW();
                                    END");

        $connection->executeQuery("SET GLOBAL event_scheduler='ON'");

        $io->success('La base de donnée à été initialisée !');

        return Command::SUCCESS;
    }
}
