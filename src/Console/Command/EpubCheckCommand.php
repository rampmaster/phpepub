<?php
namespace Rampmaster\EPub\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class EpubCheckCommand extends Command {

    public const NAME = 'epubcheck:validate';
    public const DESCRIPTION = 'Validate an EPUB with epubcheck (binary or jar)';

    public function __construct()
    {
        parent::__construct(self::NAME, self::DESCRIPTION);
    }

    protected function configure(): void {
        // Configure command: optional file argument
        $this->setHelp('Validate an EPUB file using the system epubcheck binary or a local epubcheck JAR.\n\nUsage: php bin/console epubcheck:validate <file>\nOr set EPUB_FILE environment variable.');
        $this->addArgument('file', InputArgument::OPTIONAL, 'EPUB file to validate');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $file = $input->getArgument('file');

        // Determine binary path (use Process to probe which)
        $binary = '';
        try {
            $probe = new Process(['which', 'epubcheck']);
            $probe->run();
            if ($probe->isSuccessful()) {
                $binary = trim($probe->getOutput());
            }
        } catch (\Throwable $e) {
            // ignore probe errors, fallback to jar search
        }

        if (empty($file)) {
            $file = getenv('EPUB_FILE') ?: null;
        }

        if (empty($file)) {
            $output->writeln('<error>No file specified. Use argument or set EPUB_FILE env var.</error>');
            return Command::INVALID;
        }

        if (!is_file($file)) {
            $output->writeln('<error>File not found: ' . $file . '</error>');
            return Command::INVALID;
        }

        if (!empty($binary)) {
            $p = new Process([$binary, $file]);
            $p->setTimeout(300);
            try {
                $p->run(function ($type, $buffer) use ($output) {
                    $output->write($buffer);
                });
            } catch (\Throwable $e) {
                $output->writeln('<error>Failed to execute epubcheck binary: ' . $e->getMessage() . '</error>');
                return Command::FAILURE;
            }
            return $p->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
        }

        // try jar
        $candidates = [__DIR__ . '/../../../epubcheck/epubcheck.jar', '/opt/epubcheck/epubcheck.jar'];
        foreach ($candidates as $jar) {
            if (is_file($jar)) {
                $p = new Process(['java', '-jar', $jar, $file]);
                $p->setTimeout(300);
                try {
                    $p->run(function ($type, $buffer) use ($output) {
                        $output->write($buffer);
                    });
                } catch (\Throwable $e) {
                    $output->writeln('<error>Failed to execute epubcheck JAR: ' . $e->getMessage() . '</error>');
                    return Command::FAILURE;
                }
                return $p->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
            }
        }

        // fallback: search under ./epubcheck
        $base = __DIR__ . '/../../../epubcheck';
        if (is_dir($base)) {
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base));
            foreach ($it as $f) {
                if ($f->isFile() && preg_match('/epubcheck.*\\.jar$/i', $f->getFilename())) {
                    $p = new Process(['java', '-jar', $f->getPathname(), $file]);
                    $p->setTimeout(300);
                    try {
                        $p->run(function ($type, $buffer) use ($output) {
                            $output->write($buffer);
                        });
                    } catch (\Throwable $e) {
                        $output->writeln('<error>Failed to execute epubcheck JAR: ' . $e->getMessage() . '</error>');
                        return Command::FAILURE;
                    }
                    return $p->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
                }
            }
        }

        $output->writeln('<error>No epubcheck installation found (binary or jar).</error>');
        return Command::FAILURE;
    }
}
