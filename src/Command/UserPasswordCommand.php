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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordCommand extends  Command {
	public function __construct(private EntityManagerInterface $em, private UserPasswordHasherInterface $hasher) {
		parent::__construct();
	}
	protected function configure(): void {
		$this
			->setName('maf:user:password')
			->setDescription('Update a user\'s password')
			->setDefinition([
				new InputArgument('user', InputArgument::REQUIRED, 'User\'s ID or username (in that order of preference'),
				new InputArgument('password', InputArgument::REQUIRED, 'User\'s new password'),
			])
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$username = $input->getArgument('user');
		$password = $input->getArgument('password');
		if (strlen($password) < 8) {
			$output->writeln("Passwords must be at least 8 characters long");
			return Command::FAILURE;
		} elseif (strlen($password) > 4096) {
			$output->writeln("Passwords must be less than 4096 characters long");
			return Command::FAILURE;
		}
		$em = $this->em;
		if ($user = $em->getRepository(User::class)->findOneBy(['id'=>$username])) {
			$output->writeln("Unable to locate user with id of '$username', trying search by username...");
		} elseif ($user = $em->getRepository(User::class)->findOneBy(['username'=>$username])) {
			$output->writeln("Unable to locate user with username of '$username'!");
			return Command::FAILURE;
		}
		$username = $user->getUsername();
		$id = $user->getId();
		$user->setPassword($this->hasher->hashPassword($user, $password));
		$user->setLastLogin(new \DateTime());
		$em->flush();
		$output->writeln("Password for $username ($id) has been updated!");
		return Command::SUCCESS;
	}

	protected function interact(InputInterface $input, OutputInterface $output): void {
		$helper = $this->getHelper('question');
		if (!$input->getArgument('user')) {
			$need = new Question('Please supply a username or ID for the user: ');
			$need->setValidator(function ($username) {
				if (empty($username)) {
					throw new Exception('Username cannot be empty!');
				}
				return $username;
			});
			$input->setArgument('user', $helper->ask($input, $output, $need));
		}
		if (!$input->getArgument('password')) {
			$need = new Question('Please supply a new password for the user: ');
			$need->setHidden(false);
			$need->setHiddenFallback(false);
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
