<?php

namespace App\Command;

use App\Service\DataImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-team',
    description: 'Importing teams from the excel file',
)]
class ImportTeamCommand extends Command
{
    private const BASE_FILE_NAME = 'data.xlsx';

    public function __construct(private readonly DataImportService $dataImportService, string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addOption('fileName', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fileName = $input->getOption('fileName') ?: self::BASE_FILE_NAME;
        $filePath = __DIR__ . '/../../public/import/'. $fileName;
        $errors = $this->dataImportService->importXlsxData($filePath);

        if ($errors) {
            foreach ($errors as $error) {
                $io->warning('Please check property: '. $error);
            }
        }

        $io->success('End of our awesome command. Enjoy!!!');

        return Command::SUCCESS;
    }
}
