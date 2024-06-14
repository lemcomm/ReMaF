<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class PromoteUserCommand extends  Command {

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct();
	}
	protected function configure() {
		$this
			->setName('maf:user:promote')
			->setDescription('Adds a role to a user')
			->setDefinition([
				new InputArgument('username', InputArgument::REQUIRED, 'User\'s username'),
				new InputArgument('role', InputArgument::REQUIRED, 'Role to add'),
			])
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$username = $input->getArgument('username');
		$role = $input->getArgument('role');
		$em = $this->em;
		$user = $em->getRepository(User::class)->findOneBy(['username'=>$username]);
		if (!$user) {
			$output->writeln("Username of '$username' is already in use.");
			return Command::FAILURE;
		}
		$user->addRole($role);
		$em->flush();
		return Command::SUCCESS;
	}

	protected function interact(InputInterface $input, OutputInterface $output) {
		$helper = $this->getHelper('question');
		if (!$input->getArgument('username')) {
			$need = new Question('User(name) to promote: ');
			$need->setValidator(function ($username) {
				if (empty($username)) {
					throw new \Exception('Username cannot be empty!');
				}
				return $username;
			});
			$input->setArgument('username', $helper->ask($input, $output, $need));
		}
		if (!$input->getArgument('role')) {
			$need = new Question('Please supply a role to be added: ');
			$need->setValidator(function ($role) {
				if (empty($role)) {
					throw new \Exception('Role cannot be empty!');
				}
				return $role;
			});
			$input->setArgument('role', $helper->ask($input, $output, $need));
		}
	}
}
