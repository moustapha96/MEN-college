<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\HttpKernel\KernelInterface;

class WebSocketServerController extends AbstractController
{

    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }


    // #[Route('/websocket/server', name: 'websocket_server')]
    // public function start(): Response
    // {
    //     // $application = new ConsoleApplication($this->kernel, $this->kernel->getEnvironment());
    //     // $command = $application->find('websocket:serve');
    //     // $input = new ArrayInput(['command' => 'websocket:serve']);
    //     // $output = new BufferedOutput();

    //     // $command->run($input, $output);

    //     // return new Response($output->fetch());
    // }
}
