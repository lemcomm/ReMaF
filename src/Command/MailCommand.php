<?php

namespace App\Command;

use App\Service\MailManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/* It may be worthwhile to actually update this into a full fledged command for including files to use as content to send in custom emails.
Shouldn't be too hard to do. --Andrew 20170507 */

class MailCommand extends Command {

	private MailManager $mm;

	public function __construct(MailManager $mm) {
		$this->mm = $mm;
		parent::__construct();
	}
	protected function configure() {
		$this
			->setName('maf:mail')
			->setDescription('Process internal mail spool and send email to users')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->mm->sendEventEmails();
	}


}
