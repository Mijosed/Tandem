<?php

namespace App\Command;

use App\Service\StripeService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:stripe:setup',
    description: 'Configure les prix Stripe pour les abonnements premium'
)]
class StripeSetupCommand extends Command
{
    public function __construct(
        private StripeService $stripeService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Configuration des prix Stripe');

        try {
            $io->section('Création des prix dans Stripe...');
            
            $prices = $this->stripeService->createPrices();
            
            $io->success('Prix créés avec succès !');
            
            $io->table(
                ['Plan', 'ID Prix', 'Montant'],
                [
                    ['Mensuel', $prices['monthly']->id, '9,99€/mois'],
                    ['Annuel', $prices['yearly']->id, '99€/an']
                ]
            );
            
            $io->note([
                'Vous pouvez maintenant utiliser ces IDs de prix dans votre application :',
                '',
                'Prix mensuel : ' . $prices['monthly']->id,
                'Prix annuel : ' . $prices['yearly']->id,
                '',
                'N\'oubliez pas de mettre à jour votre fichier .env avec ces IDs.'
            ]);

            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('Erreur lors de la configuration Stripe : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
