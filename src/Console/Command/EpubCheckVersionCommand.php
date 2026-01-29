<?php
namespace Rampmaster\EPub\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(name: self::NAME, description: self::DESCRIPTION)]
class EpubCheckVersionCommand extends Command {

    public const NAME = 'epubcheck:version';
    public const DESCRIPTION = 'Show epubcheck version (binary or jar)';

    protected function execute(InputInterface $input, OutputInterface $output): int {

        // Determine binary path via Process probe
        try {
            $probe = new Process(['which', 'epubcheck']);
            $probe->run();
            if ($probe->isSuccessful()) {
                $binary = trim($probe->getOutput());
            } else {
                $binary = '';
            }
        } catch (\Throwable $e) {
            $binary = '';
        }

        if (!empty($binary)) {
            $p = new Process([$binary, '--version']);
            try {
                $p->run();
                $output->write($p->getOutput() . $p->getErrorOutput());
                return $p->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
            } catch (\Throwable $e) {
                $output->writeln('<error>Failed to execute epubcheck binary: ' . $e->getMessage() . '</error>');
                return Command::FAILURE;
            }
        }

        // check for jar
        $candidates = [__DIR__ . '/../../../epubcheck/epubcheck.jar', '/opt/epubcheck/epubcheck.jar'];
        foreach ($candidates as $jar) {
            if (is_file($jar)) {
                $p = new Process(['java', '-jar', $jar, '--version']);
                $p->run();
                $output->write($p->getOutput() . $p->getErrorOutput());
                return $p->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
            }
        }

        $output->writeln('No epubcheck binary or jar found');
        return Command::FAILURE;
    }
}
