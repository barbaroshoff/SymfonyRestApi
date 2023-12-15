<?php

namespace App\Command;


use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateMillionBooksCommand extends Command
{
    protected static $defaultName = 'app:generate-million-books';
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    protected function configure(): void
    {
        $this->setDescription('Generate a million dummy book records');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '512M');
        $batchSize = 1000;
        $totalRecords = 1000000;
        $batches = ceil($totalRecords / $batchSize);

        for ($batch = 1; $batch <= $batches; ++$batch) {
            $this->insertBatch($batch, $batchSize);
            $output->writeln("Inserted " . ($batch * $batchSize) . " records.");
        }

        $output->writeln('All records inserted successfully.');

        return Command::SUCCESS;
    }

    private function insertBatch(int $batchNumber, int $batchSize): void
    {
        $query = 'INSERT INTO book (title, author, description, price) VALUES ';
        $parameters = [];

        for ($i = 1; $i <= $batchSize; ++$i) {
            $parameters[] = [
                'title' => 'Book Title ' . (($batchSize * ($batchNumber - 1)) + $i),
                'author' => 'Author ' . (($batchSize * ($batchNumber - 1)) + $i),
                'description' => 'Description ' . (($batchSize * ($batchNumber - 1)) + $i),
                'price' => rand(10, 100),
            ];
        }

        $valuePlaceholders = implode(',', array_fill(0, $batchSize, '(?, ?, ?, ?)'));
        $query .= $valuePlaceholders;

        $values = [];
        foreach ($parameters as $paramSet) {
            foreach ($paramSet as $paramValue) {
                $values[] = $paramValue;
            }
        }

        $statement = $this->connection->prepare($query);
        $statement->execute($values);
    }
}