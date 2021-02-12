<?php

namespace App\Command;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Ville;
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

    public function __construct(string $name = null,EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder,
                                SerializerInterface $serializer, HttpClientInterface $httpClient,
                                VilleRepository $villeRepository)
    {
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
        $this->serializer =$serializer;
        $this->httpClient = $httpClient;
        $this->villeRepository = $villeRepository;

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

        $io->success('La base de donnée à été initialisée !');

        return Command::SUCCESS;
    }
}
