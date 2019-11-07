<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Factory;

use Symfony\Component\DependencyInjection\Container;

class Connection
{
    private $connectionName;

    private $container;

    public function __construct(Container $container, string $connectionName)
    {
        $this->connectionName = $connectionName;
        $this->container = $container;
    }

    public function setConnection(string $connectionName)
    {
        $this->connectionName = $connectionName;
    }

    public function getConnection(): \Doctrine\DBAL\Driver\Connection
    {
        return $this->container->get(sprintf('doctrine.dbal.%s_connection', $this->connectionName));
    }
}
