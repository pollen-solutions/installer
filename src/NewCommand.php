<?php declare(strict_types=1);

namespace Pollen\Installer;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class NewCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('new')
            ->setDescription('Create a new Pollen application')
            ->addArgument('name', InputArgument::REQUIRED)
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forces install even if the directory already exists');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $directory = $name !== '.' ? getcwd() . '/' . $name : '.';

        if (!$input->getOption('force')) {
            $this->verifyApplicationDoesntExist($directory);
        }

        if ($directory === '.' && $input->getOption('force')) {
            throw new RuntimeException('Cannot use --force option when using current directory for installation!');
        }

        $composer = $this->findComposer();

        $commands = [
            $composer . " create-project pollen-solutions/skeleton \"$directory\" --no-interaction --remove-vcs --prefer-dist",
        ];

        if ($directory !== '.' && $input->getOption('force')) {
            if (PHP_OS_FAMILY === 'Windows') {
                array_unshift($commands, "(if exist \"$directory\" rd /s /q \"$directory\")");
            } else {
                array_unshift($commands, "rm -rf \"$directory\"");
            }
        }

        if (($process = $this->runCommands($commands, $input, $output))->isSuccessful()) {
            $output->writeln('  <bg=blue;fg=white> INFO </> Application ready! <options=bold>Build something amazing.</>' . PHP_EOL);
        }

        return $process->getExitCode();
    }

    /**
     * Verify that the application does not already exist.
     *
     * @param string $directory
     *
     * @return void
     */
    protected function verifyApplicationDoesntExist(string $directory): void
    {
        if ($directory !== getcwd() && (is_dir($directory) || is_file($directory))) {
            throw new RuntimeException('Application already exists!');
        }
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer(): string
    {
        $composerPath = getcwd() . '/composer.phar';

        if (file_exists($composerPath)) {
            return '"' . PHP_BINARY . '" ' . $composerPath;
        }

        return 'composer';
    }

    /**
     * Run the given commands.
     *
     * @param array $commands
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $env
     *
     * @return Process
     */
    protected function runCommands(
        array $commands,
        InputInterface $input,
        OutputInterface $output,
        array $env = []
    ): Process {
        if (!$output->isDecorated()) {
            $commands = array_map(static function ($value) {
                if (str_starts_with($value, 'chmod')) {
                    return $value;
                }

                return $value . ' --no-ansi';
            }, $commands);
        }

        if ($input->getOption('quiet')) {
            $commands = array_map(static function ($value) {
                if (str_starts_with($value, 'chmod')) {
                    return $value;
                }

                return $value . ' --quiet';
            }, $commands);
        }

        $process = Process::fromShellCommandline(implode(' && ', $commands), null, $env, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $output->writeln('  <bg=yellow;fg=black> WARN </> ' . $e->getMessage() . PHP_EOL);
            }
        }

        $process->run(function ($type, $line) use ($output) {
            $output->write('    ' . $line);
        });

        return $process;
    }
}
