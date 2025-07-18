<?php

namespace App\Command;

use App\Service\PoleEmploiService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'test:events-api',
    description: 'Test direct de l\'API Events France Travail'
)]
class TestEventsApiCommand extends Command
{
    private PoleEmploiService $poleEmploiService;

    public function __construct(PoleEmploiService $poleEmploiService)
    {
        $this->poleEmploiService = $poleEmploiService;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test API Events France Travail');

        try {
            // Test 1: Obtenir le token
            $io->section('1. Test d\'authentification');
            $token = $this->poleEmploiService->getAccessToken();
            $io->success('Token obtenu: ' . substr($token, 0, 20) . '...');

            // Test 2: Rechercher des événements
            $io->section('2. Recherche d\'événements');
            $events = $this->poleEmploiService->searchEvents([]);
            $io->info('Nombre d\'événements trouvés: ' . count($events));
            
            if (!empty($events)) {
                $io->table(
                    ['ID', 'Titre', 'Organisateur', 'Date début', 'Nb offres'],
                    array_slice(array_map(function($event) {
                        return [
                            $event['id'] ?? 'N/A',
                            substr($event['titre'] ?? 'N/A', 0, 30),
                            $event['organismeOrganisateur'] ?? 'N/A',
                            $event['dateDebut'] ?? 'N/A',
                            $event['nombreOffres'] ?? 0
                        ];
                    }, $events), 0, 5)
                );
            }

            // Test 3: Formater comme des jobs
            $io->section('3. Formatage en tant qu\'offres');
            $jobsResults = $this->poleEmploiService->searchJobs([]);
            $io->info('Nombre d\'offres formatées: ' . ($jobsResults['total'] ?? 0));

            $io->success('Tous les tests ont réussi !');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Erreur: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
