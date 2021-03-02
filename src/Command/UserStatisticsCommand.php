<?php

namespace App\Command;

use App\Repository\WorkMonthRepository;
use App\Service\WorkMonthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class UserStatisticsCommand extends Command
{
    /**
     * @var WorkMonthRepository
     */
    private $workMonthRepository;

    /**
     * @var WorkMonthService
     */
    private $workMonthService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        WorkMonthRepository $workMonthRepository,
        WorkMonthService $workMonthService,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();

        $this->workMonthRepository = $workMonthRepository;
        $this->workMonthService = $workMonthService;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setName('userStatistics:recalculate')
            ->setDescription('Deletes all user statistics and recalculates them from the stretch');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            'Do you want to delete all user statistics and recalculates them from the stretch? ',
            false
        );

        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('Command aborted');

            return 0;
        }

        $connection = $this->entityManager->getConnection();

        $updateStatement = $connection->prepare('UPDATE app_user_year_stats SET required_hours = 0, worked_hours = 0');
        $updateStatement->execute();

        foreach ($this->workMonthRepository->findAllApproved() as $workMonth) {
            $this->workMonthService->markApproved($workMonth);
        }

        $output->writeln('Command finished');

        return 0;
    }
}
