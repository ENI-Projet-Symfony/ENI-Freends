<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Ville;
use App\Repository\VilleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AppFixtures extends Fixture
{
    protected $entityManager;
    protected $encoder;
    protected $serializer;
    protected $httpClient;
    protected $villeRepository;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder,
                                SerializerInterface $serializer, HttpClientInterface $httpClient,
                                VilleRepository $villeRepository)
    {
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
        $this->serializer =$serializer;
        $this->httpClient = $httpClient;
        $this->villeRepository = $villeRepository;
    }

    public function load(ObjectManager $manager)
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeQuery("SET FOREIGN_KEY_CHECKS = 0");
        $connection->executeQuery("TRUNCATE TABLE etat");
        $connection->executeQuery("TRUNCATE TABLE campus");
        $connection->executeQuery("TRUNCATE TABLE participant");
        $connection->executeQuery("TRUNCATE TABLE ville");
        $connection->executeQuery("TRUNCATE TABLE lieu");
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

        //Mise en BDD des Ville de france
        $departements = $this->httpClient->request(
            'GET',
            "https://geo.api.gouv.fr/departements?fields=nom,code,codeRegion"
        )->toArray();


        foreach ($departements as $departement){
            $ville = new Ville();
            $ville->setNom($departement["nom"])
                ->setCodePostal($departement["code"])
            ;
            $manager->persist($ville);
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
                    $this->villeRepository->findOneBy(['id'=>rand(1,4)])
                );
            ;
            $manager->persist($cine);
        }

        $manager->flush();
    }
}
