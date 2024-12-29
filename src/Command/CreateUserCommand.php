<?php

namespace App\Command;

use App\Entity\User;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateUserCommand extends  Command {
	public function __construct(private EntityManagerInterface $em, private UserManager $userMan) {
		parent::__construct();
	}
	protected function configure(): void {
		$this
			->setName('maf:user:create')
			->setDescription('Create a new user')
			->setDefinition([
				new InputArgument('username', InputArgument::REQUIRED, 'User\'s username'),
				new InputArgument('email', InputArgument::REQUIRED, 'User\'s email'),
				new InputArgument('password', InputArgument::REQUIRED, 'User\'s password'),
			])
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$username = $input->getArgument('username');
		$email = $input->getArgument('email');
		$password = $input->getArgument('password');
		$em = $this->em;
		if ($em->getRepository(User::class)->findOneBy(['username'=>$username])) {
			$output->writeln("Username of '$username' is already in use.");
			return Command::FAILURE;
		}
		if ($em->getRepository(User::class)->findOneBy(['email'=>$email])) {
			$output->writeln("Email of '$email' is already in use.");
			return Command::FAILURE;
		}

		$user = $this->userMan->createuser();
		$user = $this->userMan->addUserDetails($user, $username, $password, $email);
		$user->setEnabled(true);
		$em->persist($user);
		$em->flush();
		return Command::SUCCESS;
	}

	protected function interact(InputInterface $input, OutputInterface $output): void {
		$helper = $this->getHelper('question');
		if (!$input->getArgument('username')) {
			$need = new Question('Please supply a username for the new user: ');
			$need->setValidator(function ($username) {
				if (empty($username)) {
					throw new Exception('Username cannot be empty!');
				}
				return $username;
			});
			$input->setArgument('username', $helper->ask($input, $output, $need));
		}
		if (!$input->getArgument('email')) {
			$need = new Question('Please supply an email for the new user: ');
			$need->setValidator(function ($email) {
				if (empty($email)) {
					throw new Exception('Email cannot be empty!');
				}
				return $email;
			});
			$input->setArgument('email', $helper->ask($input, $output, $need));
		}
		if (!$input->getArgument('password')) {
			$need = new Question('Please supply a password for the new user: ');
			$need->setValidator(function ($password) {
				if (empty($password)) {
					throw new Exception('Password cannot be empty!');
				}
				return $password;
			});
			$input->setArgument('password', $helper->ask($input, $output, $need));
		}
	}
}
