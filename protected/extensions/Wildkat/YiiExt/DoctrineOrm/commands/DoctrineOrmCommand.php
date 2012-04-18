<?php

use Symfony\Component\Console\Helper\HelperSet,
    Symfony\Component\Console\Helper\DialogHelper,
    Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper,
    Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
/**
 * DoctrineCommand.
 *
 * This command adds the Doctrine 2 CLI to the list of options in the yiic tool. You must
 * configure protected/config/console.php with the same information as set out in your
 * main.php including the component and Wildkat alias.
 *
 * This command file should then live in protected/commands
 *
 * @category YiiExtensions
 * @package  Wildkat\YiiExt\DoctrineOrm
 * @author   Kevin Bradwick <kevin@wildk.at>
 * @license  New BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: 1.0.1
 * @link     http://www.wildk.at
 */
class DoctrineOrmCommand extends CConsoleCommand
{
    /**
     * This gets executed when the command runs
     *
     * @return null
     */
    public function run()
    {
        unset($_SERVER['argv'][1]);

        $cli = $this->getCli();

        $cmd = new \Doctrine\DBAL\Tools\Console\Command\RunSqlCommand();
        $cli->add($cmd);
        $cmd = new \Doctrine\DBAL\Tools\Console\Command\ImportCommand();
        $cli->add($cmd);
        $cmd = new \Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand();
        $cli->add($cmd);
        $cmd = new \Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand();
        $cli->add($cmd);
        $cmd = new \Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand();
        $cli->add($cmd);
        $cmd = new \Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand();
        $cli->add($cmd);
        $cmd = new \Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand();
        $cli->add($cmd);
        $cmd = new \Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand();
        $cli->add($cmd);
        $cmd = new \Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand();
        $cli->add($cmd);
        $cmd = new \Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand();
        $cli->add($cmd);
        $cmd = new \Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand();
        $cli->add($cmd);
        $cmd = new \Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand();
        $cli->add($cmd);
        $cmd = new \Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand();
        $cli->add($cmd);
        $cmd = new \Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand();
        $cli->add($cmd);
        $cmd = new \Doctrine\ORM\Tools\Console\Command\RunDqlCommand();
        $cli->add($cmd);

        $cli->run();

    }//end run()

    /**
     * Returns the default entity manager
     *
     * @return Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return Yii::app()->doctrine->getEntityManager();

    }//end getEntityManager()

    /**
     * Get the cli object
     *
     * @return Symfony\component\Console\Application
     */
    protected function getCli()
    {
        $em = $this->getEntityManager();

        $helperSet = new HelperSet(
            array(
                'db'     => new ConnectionHelper($em->getConnection()),
                'em'     => new EntityManagerHelper($em),
                'dialog' => new DialogHelper(),
            )
        );

        $cli = new Symfony\Component\Console\Application(
            'Doctrine Command Line Interface',
            Doctrine\Common\Version::VERSION
        );

        $cli->setHelperSet($helperSet);
        $cli->setCatchExceptions(true);

        return $cli;

    }//end getCli()
}//end class