<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>.
 */

namespace ExampleApp\Command\Base;

use ExampleApp\DependencyInjection\IAppContainer;
use ExampleApp\Exception\FileNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var IAppContainer
     */
    protected $container;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct($commandName, IAppContainer $container)
    {
        parent::__construct($commandName);

        $this->container = $container;
    }

    final protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->registerDriver();
        $this->registerSubscribers();
        $this->doExecute();
    }

    abstract protected function registerDriver();

    private function registerSubscribers()
    {
        if (!$this->input->hasOption('with-subscriber')) {
            return;
        }

        $subscribers = (array) $this->input->getOption('with-subscriber');
        $dispatcher = $this->container->getDispatcher();

        foreach ($subscribers as $subscriberClass) {
            $subscriber = new $subscriberClass();
            $dispatcher->registerSubscriber($subscriber);
        }
    }

    abstract protected function doExecute();

    final protected function configure()
    {
        $this->addOption('with-subscriber', 's', InputArgument::IS_ARRAY, null, []);
        $this->doConfigure();
    }

    abstract protected function doConfigure();

    protected function addFileArgument()
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'File path to upload.');

        return $this;
    }

    protected function addIdArgument()
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'The id of object which has file.');

        return $this;
    }

    protected function getId()
    {
        return (int) $this->input->getArgument('id');
    }

    protected function getFile()
    {
        $file = $this->input->getArgument('file');

        if (!file_exists($file)) {
            throw new FileNotFoundException($file);
        }

        return new \SplFileInfo($file);
    }

    /**
     * @param int|null $id
     * @param array    $fileReference
     */
    protected function view($id = null, array $fileReference = null)
    {
        $this->output->write(json_encode(compact('id', 'fileReference')));
    }
}
