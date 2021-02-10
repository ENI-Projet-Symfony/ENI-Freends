<?php

namespace App\Command;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\DateTime;

class SortieFininscriptionCommand extends Command
{
    protected $entityManager;
    protected $sortieRepository;
    protected $etatRepository;
    protected static $defaultName = 'app:sortie:fininscription';
    protected static $defaultDescription = 'Commande effectuée par Cron Job afin de mettre à jour les sorties';

    public function __construct(string $name = null,EntityManagerInterface $entityManager, SortieRepository $sortieRepository, EtatRepository $etatRepository)
    {
        $this->entityManager = $entityManager;
        $this->sortieRepository = $sortieRepository;
        $this->etatRepository = $etatRepository;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $em = $this->entityManager;
        $now = new \DateTime();
        $nowFormat = new \DateTime($now->format('Y-M-d'). "00:00:00");

        $sortieEnCours = $this->sortieRepository->findBy(['etat' => 2]);
        $etatClos = $this->etatRepository->find(['id' => 3]);

        foreach ($sortieEnCours as $sortie)
        {
            if($sortie->getDateLimiteInscription() <=  $nowFormat){
                $sortie->setEtat($etatClos);
                $em->persist($sortie);
            }
        }
        $em->flush();

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
