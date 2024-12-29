<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ActivateUserCommand extends  Command {
	public function __construct(private EntityManagerInterface $em) {
		parent::__construct();
	}
	protected function configure(): void {
		$this
			->setName('maf:user:activate')
			->setDescription('Manually activate a user')
			->setDefinition([
				new InputArgument('username', InputArgument::REQUIRED, 'User\'s username'),
			])
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$username = $input->getArgument('username');
		$em = $this->em;
		$user = $em->getRepository(User::class)->findOneBy(['username'=>$username]);
		if (!$user) {
			$output->writeln("Unable to locate user with username of '$username'");
			return Command::FAILURE;
		}
		$user->setEnabled(true);
		$em->flush();
		return Command::SUCCESS;
	}

	protected function interact(InputInterface $input, OutputInterface $output): void {
		$helper = $this->getHelper('question');
		if (!$input->getArgument('username')) {
			$need = new Question('Please supply a username of a user to activate: ');
			$need->setValidator(function ($username) {
				if (empty($username)) {
					throw new Exception('Username cannot be empty!');
				}
				return $username;
			});
			$input->setArgument('username', $helper->ask($input, $output, $need));
		}
	}
}
