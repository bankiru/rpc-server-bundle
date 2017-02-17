<?php

namespace Bankiru\Api\Rpc\Command;

use Bankiru\Api\Rpc\Routing\Route;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RouterDebugCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('debug:rpc_router');
        $this->setDescription('Display essential info about RPC routing');
        $this->addOption('endpoint',
                         'p',
                         InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                         'Filter endpoint',
                         null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->setDecorated(true);
        $table = new Table($output);

        $table->setHeaders(['endpoint', 'method', 'controller', 'context', 'default_context']);

        $endpointOption = $input->getOption('endpoint');

        $allRoutes = $this->getRoutes();
        /** @var Route[][] $sortedRoutes */
        $sortedRoutes = [];
        foreach ($allRoutes as $endpoint => $routes) {
            foreach ($routes as $route) {
                $sortedRoutes[$endpoint][$route->getMethod()] = $route;
            }
            ksort($sortedRoutes[$endpoint]);
        }

        foreach ($sortedRoutes as $endpoint => $routes) {
            if (count($endpointOption) !== 0 && !in_array($endpoint, $endpointOption, true)) {
                continue;
            }
            foreach ($routes as $route) {
                $table->addRow(
                    [
                        $endpoint,
                        $route->getMethod(),
                        $route->getController(),
                        implode(',', $route->getContext()),
                        $route->includeDefaultContext() ? 'true' : 'false',
                    ]
                );
            }
        }

        $table->render();
    }

    /**
     * @return Route[][]
     */
    protected function getRoutes()
    {
        $routes = [];

        $collection = $this->getContainer()->get('rpc.router.collection');
        foreach ($collection->all() as $endpoint => $router) {
            $routes[$endpoint] = $router->getCollection()->all();
        }

        return $routes;
    }
}
