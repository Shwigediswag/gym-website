<?php

declare(strict_types=1);

namespace App\Settings\Command;

use App\Settings\SiteSettings;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:settings:sync',
    description: 'Insert any missing site settings with their placeholder defaults (run after deploys).',
)]
final class SyncSettingsCommand extends Command
{
    public function __construct(private readonly SiteSettings $settings)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $created = $this->settings->sync();
        (new SymfonyStyle($input, $output))->success(sprintf('%d setting(s) created.', $created));

        return Command::SUCCESS;
    }
}
