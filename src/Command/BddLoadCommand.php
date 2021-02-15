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
            ->setCampus($campus2);
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
            ->setCampus($campus2);
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
            ->setCampus($campus2);
        $manager->persist($arkoSuperAdmin);

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

        //Sortie test de passage de l'état 2 vers l'etat 3
        $dateDebut1 = new \DateTime();
        $dateDebut1->add(new \DateInterval('P0Y0M7DT0H0M0S'));
        $dateDebutSortie1 = new \DateTime($dateDebut1->format('Y-M-d H:i:s'));
        $dateIns1 = new \DateTime();
        $dateIns1->sub(new \DateInterval('P0Y0M1DT0H0M0S'));
        $dateInsSortie1 = new \DateTime($dateIns1->format('Y-M-d H:i:s'));

        $sortie1 = new Sortie();
        $sortie1->setNom("Test etat 2 vers 3")
            ->setDateHeureDebut($dateDebutSortie1)
            ->setDuree(30)
            ->setDateLimiteInscription($dateInsSortie1)
            ->setNbInscriptionsMax(20)
            ->setInfosSortie("Blablabla test")
            ->setEtat($etat2)
            ->setOrganisateur($amidenSuperAdmin)
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
        $sortie2->setNom("Test etat 3 vers 4")
            ->setDateHeureDebut($dateDebutsortie2)
            ->setDuree(240)
            ->setDateLimiteInscription($dateInssortie2)
            ->setNbInscriptionsMax(20)
            ->setInfosSortie("Blablabla test")
            ->setEtat($etat3)
            ->setOrganisateur($amidenSuperAdmin)
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
        $sortie3->setNom("Test etat 4 vers 5")
            ->setDateHeureDebut($dateDebutsortie3)
            ->setDuree(30)
            ->setDateLimiteInscription($dateInssortie3)
            ->setNbInscriptionsMax(20)
            ->setInfosSortie("Blablabla test")
            ->setEtat($etat4)
            ->setOrganisateur($amidenSuperAdmin)
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
        $sortie4->setNom("Test etat 5 vers 7")
            ->setDateHeureDebut($dateDebutsortie4)
            ->setDuree(30)
            ->setDateLimiteInscription($dateInssortie4)
            ->setNbInscriptionsMax(20)
            ->setInfosSortie("Blablabla test")
            ->setEtat($etat5)
            ->setOrganisateur($amidenSuperAdmin)
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

        $io->success('La base de donnée à été initialisée !');

        return Command::SUCCESS;
    }
}
