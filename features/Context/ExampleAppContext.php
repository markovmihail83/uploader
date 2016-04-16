<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use ExampleApp\Setup;
use PHPUnit_Framework_TestCase as Test;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Defines application features from the specific context.
 */
class ExampleAppContext implements Context, SnippetAcceptingContext
{
    use ContextVars;

    private $app;

    private $appContainer;

    private $fs;

    private $statusCode;

    private $output;

    private $outputData;

    private $subscribers;

    private $driver;

    public function __construct()
    {
        $projectRoot = realpath(__DIR__ . '/../../example-app');
        $this->setVar('project root', $projectRoot);
        $this->setVar('upload path', $projectRoot . '/src/Resources/public/uploads');
        $this->setVar('tmp', $projectRoot . '/var/tmp');
        $this->setVar('log', $projectRoot . '/var/log');
        $this->setVar('extra mappings path', $projectRoot . '/var/tmp/extra-mappings.yml');

        $this->app = new Application();
        $this->app->setAutoExit(false);

        $this->fs = new Filesystem();
        $this->output = new BufferedOutput();
        $this->outputData = [];
        $this->statusCode = 0;
        $this->subscribers = [];
        $this->driver = 'orm';
    }

    /**
     * @Given I have selected driver :driver
     */
    public function iHaveSelectedDriver($driver)
    {
        $this->driver = $driver;
    }

    /**
     * @Given amount of files in upload path is :count
     */
    public function amountOfFilesInUploadPathIs($count)
    {
        $files = $this->scanDirWithoutDotFiles($this->getVar('upload path'));
        Test::assertCount((int)$count, $files);
    }

    private function scanDirWithoutDotFiles($directory)
    {
        $files = new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS);

        return array_filter(
            iterator_to_array($files),
            function (\SplFileInfo $val) {
                return 0 !== strpos($val->getFilename(), '.');
            }
        );
    }

    /**
     * @Given I have got an uploaded file named :filename
     */
    public function iHaveAnUploadedFileNamed($filename)
    {
        $this->iHaveAFileNamed($filename);
        $this->iUploadTheFileVia($filename);
        $this->iShouldGetASuccessStatus();
    }

    /**
     * @Given I have a file named :filename
     * @Given I have a file named :filename and with content:
     */
    public function iHaveAFileNamed($filename, PyStringNode $content = null)
    {
        $data = $content ? $content->getRaw() : '';
        $filePath = $this->injectVars($filename);
        $this->fs->dumpFile($filePath, $data);
        Test::assertTrue(file_exists($filePath));
    }

    /**
     * @When I upload the file :filename
     */
    public function iUploadTheFileVia($filename)
    {
        $input = $this->buildInput('upload', null, $filename);
        $this->run($input, 'last uploaded %s');
    }

    /**
     * @return ArrayInput
     */
    private function buildInput($action, $id = null, $file = null)
    {
        $id = $this->injectVars($id);
        $file = $this->injectVars($file);

        $input = [
            'command' => sprintf('%s:%s', $this->driver, $action),
            '--with-subscriber' => $this->subscribers,
        ];

        if ($id) {
            $input['id'] = $id;
        }

        if ($file) {
            $input['file'] = $file;
        }

        return new ArrayInput($input);
    }

    private function run(ArrayInput $input, $varsTemplate)
    {
        $this->appContainer = Setup::setup($this->app);
        $this->statusCode = $this->app->run($input, $this->output);
        $this->outputData = [];

        if (0 !== $this->statusCode) {
            return;
        }

        $outputData = json_decode($this->output->fetch(), true);

        if (null !== $outputData) {
            $this->outputData = $outputData;
            $this->refreshVars($varsTemplate);
        }
    }

    private function refreshVars($template)
    {
        if (isset($this->outputData['id'])) {
            $this->setVar(sprintf($template, 'object id'), $this->outputData['id']);
        }

        if (!isset($this->outputData['fileReference'])) {
            return;
        }

        $fileReference = $this->outputData['fileReference'];

        if (isset($fileReference['file'])) {
            $this->setVar(sprintf($template, 'filename'), $fileReference['file']);
        }

        if (isset($fileReference['uri'])) {
            $this->setVar(sprintf($template, 'uri'), $fileReference['uri']);
        }

        if (isset($fileReference['fileInfo'])) {
            $this->setVar(sprintf($template, 'file info'), $fileReference['fileInfo']);
        }
    }

    /**
     * @Then I should get a success status
     */
    public function iShouldGetASuccessStatus()
    {
        Test::assertEquals(0, $this->statusCode, $this->getLasErrorMessage());
    }

    private function getLasErrorMessage()
    {
        return sprintf('Error occurred at last command. Error message: %s%s', PHP_EOL, $this->output->fetch());
    }

    /**
     * @When I delete the object with id :id
     */
    public function iDeleteObjectWithId($id)
    {
        $input = $this->buildInput('remove', $id);
        $this->run($input, 'last removed %s');
    }

    /**
     * @When I update object with id :id to replace the file to the new file :newFileName
     */
    public function iUpdateObjectToReplaceTheFileToTheNewFile($id, $newFileName)
    {
        $input = $this->buildInput('update', $id, $newFileName);
        $this->run($input, 'last updated %s');
    }

    /**
     * @When I get an object with id :id
     */
    public function iGetAnObjectWithId($id)
    {
        $input = $this->buildInput('get', $id);
        $this->run($input, 'last obtained %s');
    }

    /**
     * @Then I should see uri :uri
     */
    public function iShouldSeeUri($uri)
    {
        $uri = $this->injectVars($uri);
        Test::assertTrue(isset($this->outputData['fileReference']));
        Test::assertEquals($uri, $this->outputData['fileReference']['uri']);
    }

    /**
     * @Then I should see file info :fileInfo
     */
    public function iShouldSeeFileInfo($fileInfo)
    {
        $expected = $this->normalizePath($this->injectVars($fileInfo));
        Test::assertTrue(isset($this->outputData['fileReference']));
        $actual = $this->normalizePath($this->outputData['fileReference']['fileInfo']);
        Test::assertEquals($expected, $actual);
    }

    private function normalizePath($path)
    {
        if (false !== strpos($path, '://')) {
            return str_replace('\\', '/', $path);
        }

        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    /**
     * @Given I register a subscriber :subscriberClass
     */
    public function iRegisterASubscriber($subscriberClass)
    {
        $this->subscribers[] = $subscriberClass;
    }

    /**
     * @Given The file :filename is exist
     */
    public function theFileIsExist($filename)
    {
        Test::assertTrue(file_exists($this->injectVars($filename)));
    }

    /**
     * @BeforeScenario
     * @AfterScenario
     */
    public function ACleanDatabase()
    {
        @unlink($this->getVar('project root') . '/src/Resources/data/orm.sqlite');
        @unlink($this->getVar('project root') . '/src/Resources/data/orm_embeddable.sqlite');
        @unlink($this->getVar('project root') . '/src/Resources/data/dbal.sqlite');

        require __DIR__ . '/../../example-app/bin/prepare.php';
    }

    /**
     * @BeforeScenario
     * @AfterScenario
     */
    public function aCleanUploadPath()
    {
        $this->cleanDirectory($this->getVar('upload path'));
    }

    private function cleanDirectory($directory)
    {
        $files = $this->scanDirWithoutDotFiles($directory);

        $this->fs->remove($files);
    }

    /**
     * @BeforeScenario
     * @AfterScenario
     */
    public function aCleanTmpPath()
    {
        $this->cleanDirectory($this->getVar('tmp'));
    }

    /**
     * @BeforeScenario
     * @AfterScenario
     */
    public function aCleanLogPath()
    {
        $this->cleanDirectory($this->getVar('log'));
    }
}
