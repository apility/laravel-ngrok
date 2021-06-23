<?php

namespace Apility\Laravel\Ngrok;

use Symfony\Component\Process\Process;

class NgrokProcessBuilder
{
    /**
     * Current working directory.
     *
     * @var string
     */
    protected $cwd;

    /**
     * @param string $cwd
     */
    public function __construct(string $cwd = null)
    {
        $this->setWorkingDirectory($cwd);
    }

    /**
     * Set the current working directory.
     *
     * @param string $cwd
     */
    public function setWorkingDirectory(string $cwd): void
    {
        $this->cwd = $cwd;
    }

    /**
     * Get the current working directory.
     *
     * @return string
     */
    public function getWorkingDirectory(): string
    {
        return $this->cwd;
    }

    /**
     * Build ngrok command.
     *
     * @param string $host
     * @param string $port
     * @return \Symfony\Component\Process\Process
     */
    public function buildProcess(string $host = '', string $port = '80', string $region = 'eu'): Process
    {
        $command = ['ngrok', 'http', '--log', 'stdout'];

        $region = $region ? $region : env('NGROK_REGION', 'eu');

        if ($region) {
            $command[] = "-region={$region}";
        }

        if ($host !== '') {
            $command[] = '--host-header';
            $command[] = $host;
        }

        $command[] = $port ?: '80';

        return new Process($command, $this->getWorkingDirectory(), null, null, null);
    }
}
