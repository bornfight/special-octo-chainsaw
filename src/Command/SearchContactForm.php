<?php
/**
 * Created by PhpStorm.
 * User: josip
 * Date: 2019-04-11
 * Time: 13:53
 */

namespace App\Command;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchContactForm extends Command
{

    private static $USER_ARGUMENT_NAME = 'user';
    private static $PASS_ARGUMENT_NAME = 'password';
    private static $WP_POSTS = 'wp_posts';
    private static $CONTACT_FORM_TYPE = 'wpcf7_contact_form';
    private static $POST_TYPE = 'post_type';
    protected static $defaultName = 'wp:search-form';

    protected function configure()
    {
        $this->addArgument($this::$USER_ARGUMENT_NAME, InputArgument::REQUIRED, 'database username');
        $this->addArgument($this::$PASS_ARGUMENT_NAME, InputArgument::REQUIRED, 'database password');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument($this::$USER_ARGUMENT_NAME);
        $password = $input->getArgument($this::$PASS_ARGUMENT_NAME);

        try{
            $connection = $this->getConnection($username, $password, null);
        } catch(DBALException $e){
            $output->writeln($e->getMessage());
            $output->writeln('Invalid db name');
        }
        $connection->connect();

        $databases = $connection->getSchemaManager()->listDatabases();

        foreach ($databases as $database){
            $output->writeln(sprintf('CURRENT DATABASE %s', $database));
           try{
               $this->processDatabase($this->getConnection($username, $password, $database), $output);
           }catch(DBALException $e){
                $output->writeln($e->getMessage());
           }
           $output->writeln('');
           $output->writeln('');
        }

    }

    /**
     * @param $user
     * @param $password
     * @param $database
     * @return Connection
     * @throws DBALException
     */
    private function getConnection(string $user, string $password, ?string $database): Connection
    {
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = array(
            'url' => sprintf('mysql://%s:%s@localhost/%s', $user, $password, $database ?? ''),
        );

       $connection =  DriverManager::getConnection($connectionParams, $config);
       return $connection;
    }

    /**
     * @param Connection $connection
     * @param $output
     * @throws DBALException
     */
    private function processDatabase(Connection $connection, OutputInterface $output): void
    {
        $connection->connect();
        $sqlIsWpProject = sprintf('SELECT * FROM %s', $this::$WP_POSTS);

        $resIsWpProject = $connection->query($sqlIsWpProject);

        if(!$resIsWpProject->fetch()){
            $output->writeln('wp_posts is emtpy');
            return;
        }

        $sqlUsesCf7form = sprintf(
            "SELECT * FROM %s WHERE %s LIKE '%s'",
            $this::$WP_POSTS,
            $this::$POST_TYPE,
            $this::$CONTACT_FORM_TYPE
        );
        $resUsesCf7Form = $connection->query($sqlUsesCf7form);

        while ($row = $resUsesCf7Form->fetch()) {
            $content = $row['post_content'];

            if(preg_match('/Reply-To\s?:\s?(.*)/', $content, $matches)){
                $output->writeln(sprintf('Reply-To found with value %s', $matches[1]));
            } else{
                $output->writeln('Reply-To not found');
            }
        }
    }
}
