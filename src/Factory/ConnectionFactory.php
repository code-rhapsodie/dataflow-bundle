<?php

declare(strict_types=1);

namespace CodeRhapsodie\DataflowBundle\Factory;

use Symfony\Component\DependencyInjection\Container;

/**
 * Class ConnectionFactory.
 *
 * @codeCoverageIgnore
 */
class ConnectionFactory
{
    public function __construct(private Container $container, private string $connectionName)
    {
    }

    public function setConnectionName(string $connectionName)
    {
        $this->connectionName = $connectionName;
    }

    public function getConnection(): \Doctrine\DBAL\Connection
    {
        return $this->container->get(sprintf('doctrine.dbal.%s_connection', $this->connectionName));
    }
}
