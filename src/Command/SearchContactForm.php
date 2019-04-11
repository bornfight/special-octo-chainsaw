<?php
/**
 * Created by PhpStorm.
 * User: josip
 * Date: 2019-04-11
 * Time: 13:53
 */

namespace App\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchContactForm extends Command
{

    private static $ARGUMENT_NAME = 'database-url';
    protected static $defaultName = 'wp:search-form';

    protected function configure()
    {
        $this->addArgument($this::$ARGUMENT_NAME, InputArgument::REQUIRED, 'database url');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbUrl = $input->getArgument($this::$ARGUMENT_NAME);
        $config = new \Doctrine\DBAL\Configuration();

        $connectionParams = array(
            'url' => $dbUrl,
        );

        $connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

        var_dump($connection);
    }

}
