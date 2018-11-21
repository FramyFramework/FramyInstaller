#!/usr/bin/php
<?php
/**
 * FramyInstaller
 *
 * CLI tool made to make Framy application handling
 * easier.
 *
 * @copyright Copyright FramyInstaller
 * @Author Marco Bier <mrfibunacci@gmail.com>
 */

const VERSION = 'v0.1-alpha.1';

const COMMANDS = [
    'HelpCommand',
    'InstallCommand'
];

const INSTALL_DIR = '/usr/bin/FramyInstaller';

const REPO = 'https://github.com/FramyFramework/Framy.git';

error_reporting(E_WARNING);

class Argument
{
    public $name;

    public $description;

    private $value;

    function __construct(string $name, string $description)
    {
        $this->name = $name;
        $this->description= $description;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }
}

abstract class Command
{
    protected $name;

    protected $help;

    protected $arguments;
    protected $options;

    protected static $availableArguments = [];
    protected static $availableOptions = [];

    function __construct(array $options, array $arguments)
    {
        try {
            $this->configure();
            $this->setArguments($arguments);
            $this->setOptions($options);
        } catch (\Exception $e) {
            print($e->getMessage()."\n");exit();
        }
    }

    /**
     * Configures the current command.
     */
    protected function configure(){}

    public function execute(){}

    private function compareArrays(&$arr1, &$arr2): bool
    {
        return (count($arr1) == count($arr2));
    }

    /**
     * @param mixed $help
     */
    public function setHelp($help): void
    {
        $this->help = $help;
    }

    public function setArguments(array $arguments)
    {
        if(!self::compareArrays(self::$availableArguments, $arguments))
            throw new \Exception("Command argument not valid\n");
        else
            foreach ($arguments as $key => $argument) {
                self::$availableArguments[$key]->setValue($argument);
            }
            $this->arguments = $arguments;
    }

    /**
     * @param string $name
     * @return Argument|false
     */
    public function getArgument(string $name)
    {
        foreach(self::$availableArguments as $argument) {
            if($argument->name == $name)
                return $argument;
        }

        return false;
    }



    public function setOptions(array $options)
    {
        if(!self::compareArrays(self::$availableOptions, $options))
            throw new \Exception("Command options not valid\n");
        else
            $this->options = $options;
    }

    /**
     * @param string $name
     * @throws \Exception
     * @return $this
     */
    public function setName($name)
    {
        $this->validateName($name);

        $this->name = $name;

        return $this;
    }

    /**
     * Validates a command name
     *
     * It must be non-empty and parts can optionally be separated by ":".
     *
     * @param string $name
     * @throws \Exception When the name is invalid
     */
    private function validateName($name)
    {
        if(!preg_match('/^[^\:]++(\:[^\:]++)*$/', $name))
            throw new \Exception(sprintf('Command name "%s" is invalid.', $name));
    }
}

class HelpCommand extends Command
{
    protected function configure()
    {
        $this->setName("HelpCommand")
            ->setHelp("The Help command displays help for a given command.");

        self::$availableArguments = [
            new Argument("Name", "The name of the command you want to get help of")
        ];
    }

    public function execute()
    {
        print("Help Command\n");
        print("TODO: implement help command\n");
    }
}

class InstallCommand extends Command
{
    protected function configure()
    {
        $this->setName("InstallCommand");
    }

    public function execute()
    {
        print("Installing FramyInstaller...\n");
        exec("cp FramyInstaller.php ".INSTALL_DIR);
        print("Copied file to '".INSTALL_DIR."'\n");
        print("Successfully installed! Use FramyInstaller\n");
    }
}

class CreateCommand extends Command
{
    protected function configure()
    {
        $this->setName("CreateCommand")
            ->setHelp("Usage: Navigate to the directory in which 
                your project shall be located and execute: 'FramyInstall create NewProject'\n");

        self::$availableArguments = [
            new Argument("ProjectName", "Name of the newly created project\n")
        ];
    }

    public function execute()
    {
        $prjctName = $this->getArgument("ProjectName")->getValue();
        print("Creating new Project: $prjctName\n");
        exec("git clone ".REPO." $prjctName");
        print("Done navigate there using 'cd $prjctName'\n");
    }
}

class Console
{
    /**
     * @var Command
     */
    private $command;

    function __construct(array $arguments)
    {
        $this->welcomeMessage();

        try {
            $class = ucfirst($arguments[1])."Command";
            if(!class_exists($class) || $class == "Command")
                throw new \Exception();
        } catch (\Exception $e) {
            $class = "HelpCommand";
        } finally {
            $this->command = new $class($this->getOptions($arguments), $this->getArguments($arguments));
        }
    }

    private function welcomeMessage(): void
    {
        print("FramyInstaller ".VERSION."\n");
        print("The Framy Framework manager!\n\n");
    }

    private function getArguments(array $arguments):array
    {
        $arg = [];

        array_shift($arguments);
        array_shift($arguments);

        foreach ($arguments as $argument) {
            if(substr($argument, 0, 1) !== "-")
                $arg[] = $argument;
        }

        return $arg;
    }

    private function getOptions(array $arguments): array
    {
        $opt = [];

        array_shift($arguments);
        array_shift($arguments);

        foreach ($arguments as $option) {
            if(substr($option, 0, 1) === "-")
                $opt[] = $option;
        }

        return $opt;
    }

    public function run()
    {
        // Execute command
        $this->command->execute();
    }
}

$App = new Console($argv);
$App->run();