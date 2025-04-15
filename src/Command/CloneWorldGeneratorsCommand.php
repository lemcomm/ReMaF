<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

class CloneWorldGeneratorsCommand extends Command {

	public function __construct(private KernelInterface $kernel) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:clone:generators')
			->setDescription('Command to create an importable world JSON file.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$root = $this->kernel->getProjectDir();
		try {
			$output->writeln('<info>Cloning World Generators</info>');
			$dir = $root.'/src/Command/Local';
			$fs = new Filesystem();
			if (!file_exists($dir)) {
				try {
					$fs->mkdir($dir, 0755);
				} catch (Exception $e) {
					$output->writeln('<error>Could not create directory ' . $dir . '</error>');
					return COMMAND::FAILURE;
				}
			}
			try {
				$cmds = $root.'/src/Command';
				$finder = new Finder();
				foreach ($finder->in($cmds) as $file) {
					$name = $file->getFilename();
					if (str_contains($name, 'CreateWorld')) {
						$txt = file_get_contents($file->getRealPath());
						$txt = str_replace('namespace App\Command;', 'namespace App\Command\Local;', $txt);
						$txt = str_replace('CreateWorld', 'CreateLocalWorld', $txt);
						$txt = str_replace('maf:generate', 'local:generate', $txt);
						$txt = str_replace('->setHidden(true)', '->setHidden(false)', $txt);
						$name = str_replace('CreateWorld', 'CreateLocalWorld', $name);
						$fs->dumpFile($dir.'/'.$name, $txt);
						$output->writeln('<info>' . $dir.'/'.$name . ' created</info>');
					}
				}
				$output->writeln('<info>Cloning Complete!</info>');
				return Command::SUCCESS;
			} catch (Exception $e) {
				$output->writeln('<error>' . $e->getMessage() . '</error>');
				return COMMAND::FAILURE;
			}
		} catch (Exception $e) {
			$output->writeln('<error>' . $e->getMessage() . '</error>');
			return Command::FAILURE;
		}
	}
}
