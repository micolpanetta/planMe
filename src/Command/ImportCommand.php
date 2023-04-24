<?php

namespace App\Command;

use App\Importer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('import-foods')
            ->setDescription('This command imports a dataset of foods into the db')
            ->setHelp('Run this command before calling the apis');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->entityManager;
        
        $importer = new Importer();
        $foods = $importer->getFoods();
        
        foreach ($foods as $food) {
            $em->persist($food);
        }

        $em->flush();

        $output->writeln('FoodDB foods successfully imported');
        return Command::SUCCESS;
    }
}