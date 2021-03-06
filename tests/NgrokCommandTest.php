<?php

namespace Apility\Laravel\Ngrok\Tests;

use Apility\Laravel\Ngrok\NgrokProcessBuilder;
use Apility\Laravel\Ngrok\NgrokWebService;
use Apility\Laravel\Ngrok\Tests\OrchestraTestCase as TestCase;
use Symfony\Component\Process\Process;

/**
 * @testdox Ngrok command
 */
class NgrokCommandTest extends TestCase
{
    public function test_handle() : void
    {
        $host = 'example.com';
        $port = '80';

        config(['app.url' => '']);

        $tunnels = [
            [
                'public_url' => 'http://00000000.ngrok.io',
                'config' => ['addr' => 'localhost:80'],
            ],
            [
                'public_url' => 'https://00000000.ngrok.io',
                'config' => ['addr' => 'localhost:80'],
            ],
        ];

        $webService = $this->prophesize(NgrokWebService::class);
        $webService->setUrl('http://127.0.0.1:4040')->shouldBeCalled();
        $webService->getTunnels()->willReturn($tunnels)->shouldBeCalled();

        $process = $this->prophesize(Process::class);
        $process->run(\Prophecy\Argument::type('callable'))->will(function ($args) use ($process) {
            $callback = $args[0];

            $process->getOutput()->willReturn('msg="starting web service" addr=127.0.0.1:4040')->shouldBeCalled();
            $process->clearOutput()->shouldBeCalled();

            $callback(Process::OUT, 'msg="starting web service" addr=127.0.0.1:4040');

            $process->clearErrorOutput()->shouldBeCalled();

            $callback(Process::ERR, 'error');

            return 0;
        })->shouldBeCalled();
        $process->getErrorOutput()->willReturn('')->shouldBeCalled();
        $process->getExitCode()->willReturn(0)->shouldBeCalled();

        $processBuilder = $this->prophesize(NgrokProcessBuilder::class);
        $processBuilder->buildProcess($host, $port)->willReturn($process->reveal())->shouldBeCalled();

        app()->instance(NgrokWebService::class, $webService->reveal());
        app()->instance(NgrokProcessBuilder::class, $processBuilder->reveal());

        $this->artisan('ngrok', ['host' => $host, '--port' => $port])
             ->expectsOutput('Host: ' . $host)
             ->expectsOutput('Port: ' . $port)
             ->assertExitCode(0);
    }

    public function test_handle_from_config() : void
    {
        $host = 'example.com';
        $port = '8000';

        config(['app.url' => 'http://example.com:8000']);

        $tunnels = [
            [
                'public_url' => 'http://00000000.ngrok.io',
                'config' => ['addr' => 'localhost:8000'],
            ],
            [
                'public_url' => 'https://00000000.ngrok.io',
                'config' => ['addr' => 'localhost:8000'],
            ],
        ];

        $webService = $this->prophesize(NgrokWebService::class);
        $webService->setUrl('http://127.0.0.1:4040')->shouldBeCalled();
        $webService->getTunnels()->willReturn($tunnels)->shouldBeCalled();

        $process = $this->prophesize(Process::class);
        $process->run(\Prophecy\Argument::type('callable'))->will(function ($args) use ($process) {
            $callback = $args[0];

            $process->getOutput()->willReturn('msg="starting web service" addr=127.0.0.1:4040')->shouldBeCalled();
            $process->clearOutput()->shouldBeCalled();

            $callback(Process::OUT, 'msg="starting web service" addr=127.0.0.1:4040');

            $process->clearErrorOutput()->shouldBeCalled();

            $callback(Process::ERR, 'error');

            return 0;
        })->shouldBeCalled();
        $process->getErrorOutput()->willReturn('')->shouldBeCalled();
        $process->getExitCode()->willReturn(0)->shouldBeCalled();

        $processBuilder = $this->prophesize(NgrokProcessBuilder::class);
        $processBuilder->buildProcess($host, $port)->willReturn($process->reveal())->shouldBeCalled();

        app()->instance(NgrokWebService::class, $webService->reveal());
        app()->instance(NgrokProcessBuilder::class, $processBuilder->reveal());

        $this->artisan('ngrok')
             ->expectsOutput('Host: ' . $host)
             ->expectsOutput('Port: ' . $port)
             ->assertExitCode(0);
    }

    public function test_handle_invalid_host() : void
    {
        $host = 'example.com';
        $port = '8000';

        config(['app.url' => '']);

        $tunnels = [
            [
                'public_url' => 'http://00000000.ngrok.io',
                'config' => ['addr' => 'localhost:8000'],
            ],
            [
                'public_url' => 'https://00000000.ngrok.io',
                'config' => ['addr' => 'localhost:8000'],
            ],
        ];

        $webService = $this->prophesize(NgrokWebService::class);
        $webService->setUrl('http://127.0.0.1:4040')->shouldNotBeCalled();
        $webService->getTunnels()->willReturn($tunnels)->shouldNotBeCalled();

        $process = $this->prophesize(Process::class);
        $process->run(\Prophecy\Argument::type('callable'))->shouldNotBeCalled();
        $process->getErrorOutput()->willReturn('')->shouldNotBeCalled();
        $process->getExitCode()->willReturn(0)->shouldNotBeCalled();

        $processBuilder = $this->prophesize(NgrokProcessBuilder::class);
        $processBuilder->buildProcess($host, $port)->willReturn($process->reveal())->shouldNotBeCalled();

        app()->instance(NgrokWebService::class, $webService->reveal());
        app()->instance(NgrokProcessBuilder::class, $processBuilder->reveal());

        $this->artisan('ngrok')
             ->assertExitCode(1);
    }
}
