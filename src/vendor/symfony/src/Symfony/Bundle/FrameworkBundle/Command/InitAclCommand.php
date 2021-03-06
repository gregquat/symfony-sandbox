<?php

namespace Symfony\Bundle\FrameworkBundle\Command;

use Symfony\Component\Security\Acl\Dbal\Schema;

use Doctrine\DBAL\DriverManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Installs the tables required by the ACL system
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class InitAclCommand extends Command
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('init:acl')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->container->get('security.acl.dbal.connection');
        $sm = $connection->getSchemaManager();
        $tableNames = $sm->listTableNames();
        $tables = array(
            'class_table_name' => $this->container->getParameter('security.acl.dbal.class_table_name'),
            'sid_table_name'   => $this->container->getParameter('security.acl.dbal.sid_table_name'),
            'oid_table_name'   => $this->container->getParameter('security.acl.dbal.oid_table_name'),
            'oid_ancestors_table_name' => $this->container->getParameter('security.acl.dbal.oid_ancestors_table_name'),
            'entry_table_name' => $this->container->getParameter('security.acl.dbal.entry_table_name'),
        );

        foreach ($tables as $table) {
            if (in_array($table, $tableNames, true)) {
                $output->writeln(sprintf('The table "%s" already exists. Aborting.', $table));
                return;
            }
        }

        $schema = new Schema($tables);
        foreach ($schema->toSql($connection->getDatabasePlatform()) as $sql) {
            $connection->exec($sql);
        }

        $output->writeln('ACL tables have been initialized successfully.');
    }
}
