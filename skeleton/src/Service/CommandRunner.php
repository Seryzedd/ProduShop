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

        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        chdir($this->kernel->getProjectDir());

        try {
            $arguments = array_merge(['command' => $commandName], $arguments);

            $input = new ArrayInput($arguments);

            // You can use NullOutput() if you don't need the output
            $output = new BufferedOutput();
            $exitCode = $application->run($input, $output);

            // return the output, don't use if you used NullOutput()
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
            chdir($originalDir);
        }

        

        
    }
}