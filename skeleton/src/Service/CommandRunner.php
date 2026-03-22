<?php

namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CommandRunner
{
    public function __construct(private KernelInterface $kernel) {}

    public function run(string $commandName, ?array $arguments = [])
    {
        $originalDir = getcwd();
        chdir($this->kernel->getProjectDir());

        // ✅ Kernel isolé au lieu de réutiliser le kernel HTTP
        $kernelClass = get_class($this->kernel);
        $isolatedKernel = new $kernelClass(
            $this->kernel->getEnvironment(),
            $this->kernel->isDebug()
        );

        $application = new Application($isolatedKernel);
        $application->setAutoExit(false);

        try {
            $input = new ArrayInput(array_merge(['command' => $commandName], $arguments ?? []));
            $output = new BufferedOutput();
            $exitCode = $application->run($input, $output);
            $content = $output->fetch();

            if ($exitCode !== 0) {
                throw new \RuntimeException(sprintf(
                    'Command "%s" failed with exit code %d: %s',
                    $commandName,
                    $exitCode,
                    $content
                ));
            }

            return $content;
        } finally {
            $isolatedKernel->shutdown(); // ✅ Nettoyage propre
            chdir($originalDir);
        }
    }
}