<?php 
namespace Helpers;

class ArgvHandler
{
    private $args;
    private $argsKeys = ['searchString'];
    const OBJECT_MODE = 'Object';

    public function __construct($consoleArgs)
    {
        $this->handleConsoleArgs($consoleArgs);
        $this->args = $consoleArgs;
        $this->handleParameters($consoleArgs);
    }

    /**
     * Remove first argv parameter
     * @param $consoleArgs
     */
    private function handleConsoleArgs(&$consoleArgs)
    {
        array_shift($consoleArgs);
    }


    /**
     * create public property on each argv if we are in object mode
     * @param $consoleArgs
     */
    private function handleParameters($consoleArgs)
    {
         $this->args = array_combine($this->argsKeys, $consoleArgs);
         if (self::OBJECT_MODE === DEFAULT_ARGV_HELPER_MODE) {
            foreach($this->args as $key => $value) {
                $this->{$key} = $value;
            }
         }
    }

    /**
     * helper to get argv parameter by name in array mode
     * @param $parameterName
     * @return mixed
     */
    public function getParameter($parameterName)
    {
        return $this->args[$parameterName];
    }


    /**
     * helper to get all argv parameters
     * @return mixed
     */
    public function getArgv()
    {
        return $this->args;
    }
}