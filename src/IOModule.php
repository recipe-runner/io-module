<?php

namespace RecipeRunner\IOModule;

use InvalidArgumentException;
use RecipeRunner\RecipeRunner\Module\Invocation\ExecutionResult;
use RecipeRunner\RecipeRunner\Module\Invocation\Method;
use RecipeRunner\RecipeRunner\Module\ModuleBase;
use Yosymfony\Collection\CollectionInterface;

class IOModule extends ModuleBase
{
    public function __construct()
    {
        parent::__construct();
        $this->addMethodHandler('write', [$this, 'write']);
        $this->addMethodHandler('ask', [$this, 'ask']);
        $this->addMethodHandler('ask_yes_no', [$this, 'askConfirmation']);
    }

    /**
     * {@inheritdoc}
     */
    public function runMethod(Method $method, CollectionInterface $recipeVariables) : ExecutionResult
    {
        return $this->runInternalMethod($method, $recipeVariables);
    }
    
    /**
     * Write a message to the output.
     *
     * ```yaml
     * write: "Hi user"
     * ```
     * or
     * ```yaml
     * write:
     *   "Hey"
     *   "You rock!"
     * ```
     *
     * @param Method
     *
     * @return ExecutionResult Result with an empty JSON.
     */
    protected function write(Method $method): ExecutionResult
    {
        foreach ($method->getParameters() as $message) {
            $this->getIO()->write($message);
        }

        return new ExecutionResult();
    }

    /**
     * Ask a question to the user.
     *
     * ```yaml
     * ask: "What's your name?"
     * ```
     * or
     * ```yaml
     * ask:
     *   question: "What's your name?"
     *   default: "Jack"
     * ```
     *
     * @param Method
     *
     * @return ExecutionResult Result with the following JSON:
     *
     * ```json
     * {
     *   "response": "bla bla"
     * }
     * ```
     */
    protected function ask(Method $method): ExecutionResult
    {
        $parameterCounter = count($method->getParameters());

        if ($parameterCounter == 0 || $parameterCounter > 2) {
            throw new InvalidArgumentException("Method \"{$method->getName()}\" only support 1 or 2 parameters.");
        }

        $question = $method->getParameterNameOrPosition('question', 0);
        $default = $method->getParameterNameOrPosition('default', 1, '');

        $response = $this->getIO()->ask($question, $default);
        $jsonResponse = \json_encode(['response' => $response]);

        return new ExecutionResult($jsonResponse);
    }

    /**
     * Ask a yes/no question to the user.
     * Values accepted as response:
     *   true: true, "true", "yes", "1", 1
     *   false: false, "false", "no", "0", 0
     * Default value: true.
     *
     * ```yaml
     * ask_yes_no: "Are you sure?"
     * ```
     * or
     * ```yaml
     * ask_yes_no:
     *   question: "What's your name?"
     *   default: true
     * ```
     *
     * @param Method
     *
     * @return ExecutionResult Result with the following JSON:
     *
     * ```json
     * {
     *   "response": true # boolean value
     * }
     * ```
     */
    protected function askConfirmation(Method $method): ExecutionResult
    {
        $parameterCounter = count($method->getParameters());

        if ($parameterCounter == 0 || $parameterCounter > 2) {
            throw new InvalidArgumentException("Method \"{$method->getName()}\" only support 1 or 2 parameters.");
        }

        $question = $method->getParameterNameOrPosition('question', 0);
        $default = $method->getParameterNameOrPosition('default', 1, true);
        $parsedDefault = $this->parseConfirmationDefaultValue($default, $method->getName());

        $response = $this->getIO()->askConfirmation($question, $parsedDefault);
        $jsonResponse = \json_encode(['response' => $response]);

        return new ExecutionResult($jsonResponse);
    }

    private function parseConfirmationDefaultValue($value, string $methodName): bool
    {
        if (\is_bool($value)) {
            return $value;
        }

        if ($value === 1) {
            return true;
        }

        if ($value === 0) {
            return false;
        }

        switch ($value) {
            case 'true':
            case 'yes':
            case '1':
                return true;

            case 'false':
            case 'no':
            case '0':
                return false;
        }

        throw new InvalidArgumentException("Method \"{$methodName}\" only support boolean values as default parameter.");
    }
}
