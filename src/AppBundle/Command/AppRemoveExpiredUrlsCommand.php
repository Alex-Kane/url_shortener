<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AppRemoveExpiredUrlsCommand extends ContainerAwareCommand
{
    /**
     * Set params to command
     * 
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:remove-expired-urls')
             ->setDescription('Remove expired urls')
             ->setHelp('After execution this command all expired urls will be removed from database');
    }

    /**
     * Execution workflow
     * 
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        try {
            $entityManager->getRepository('AppBundle:Url')->deleteExpired();
            $output->writeln('Urls was deleted successfully!');
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }
}
