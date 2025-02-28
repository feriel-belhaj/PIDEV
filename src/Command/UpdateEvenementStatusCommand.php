<?php

namespace App\Command;

use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateEvenementStatusCommand extends Command
{
    protected static $defaultName = 'app:update-evenement-status';

    public function __construct(
        private EvenementRepository $evenementRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $evenements = $this->evenementRepository->findAll();
        $updated = 0;

        foreach ($evenements as $evenement) {
            if ($evenement->isTermine() && $evenement->getStatus() !== 'termine') {
                $evenement->updateStatus();
                $updated++;
            }
        }

        $this->entityManager->flush();
        $output->writeln(sprintf('Mise à jour de %d événements.', $updated));

        return Command::SUCCESS;
    }
} 