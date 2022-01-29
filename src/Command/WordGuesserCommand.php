<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\WordGuesserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class WordGuesserCommand extends Command
{
    protected static $defaultName = 'app:word:guesser';
    protected static $defaultAlias = 'a:w:g';

    private $wordGuesserService;

    public function __construct(WordGuesserService $wordGuesserService, string $name = null)
    {
        parent::__construct($name);
        $this->wordGuesserService = $wordGuesserService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Give a list of matching words')
            ->setAliases([self::$defaultAlias])
            ->addOption('pattern', 'p', InputOption::VALUE_REQUIRED, 'The pattern to guess')
            ->addOption('includes', 'i', InputOption::VALUE_OPTIONAL, 'The list of mandatory extra characters', '')
            ->addOption('excludes', 'x', InputOption::VALUE_OPTIONAL, 'The list of excluded characters', '')
            ->addOption('misplaced', 'm', InputOption::VALUE_OPTIONAL, 'The list of misplaced characters', '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $matches = $this->wordGuesserService->guess(
            $input->getOption('pattern'),
            str_split($input->getOption('includes')),
            str_split($input->getOption('excludes')),
            json_decode($input->getOption('misplaced'), true)
        );

        $io->success(sprintf('Found %d words', count($matches)));
        $io->table(['Word(s)'], array_map(static function($item) {return [$item];}, $matches));

        return 0;
    }
}
