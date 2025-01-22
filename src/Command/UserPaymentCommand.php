<?php

namespace App\Command;

use App\Entity\User;
use App\Service\PaymentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class UserPaymentCommand extends  Command {

	private EntityManagerInterface $em;
	private PaymentManager $pay;

	public function __construct(EntityManagerInterface $em, PaymentManager $pay) {
		$this->em = $em;
		$this->pay = $pay;
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:payment:user')
			->setDescription('Manually process a payment')
			->addArgument('user', InputArgument::REQUIRED, 'user email or id')
			->addArgument('type', InputArgument::REQUIRED, 'type (e.g. "PayPal Payment")')
			->addArgument('amount', InputArgument::REQUIRED, 'amount (in USD) to credit, will be multiplied by 100')
			->addArgument('id', InputArgument::REQUIRED, 'transaction id')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$em = $this->em;
		$pm = $this->pay;

		$u = $input->getArgument('user');
		if (intval($u)) {
			$user = $em->getRepository(User::class)->findOneBy(['id'=>intval($u)]);
		} else {
			$user = $em->getRepository(User::class)->findOneBy(['email'=>$u]);
		}
		if (!$user) {
			throw new \Exception("Cannot find user $u");
		}

		$type = $input->getArgument('type');
		$amount = (float) $input->getArgument('amount');
		$id = $input->getArgument('id');

		$output->writeln("Manually processing a $type payment for ".$user->getUsername()." of $amount USD.");

		$pm->account($user, $type, $amount, $id);

		$em->flush();
		$output->writeln("Done. User account now hold ".$user->getCredits()." credits.");
		return Command::SUCCESS;
	}


}
