<?php

namespace RecipeRunner\IOModule\Test;

use PHPUnit\Framework\TestCase;
use RecipeRunner\IOModule\IOModule;
use RecipeRunner\RecipeRunner\IO\IOInterface;
use RecipeRunner\RecipeRunner\Module\Invocation\Method;
use Yosymfony\Collection\MixedCollection;

class IOModuleTest extends TestCase
{
    public function testMethodWriteMustWriteAMessageToTheOutput(): void
    {
        $message = 'Hi Mr. Robot';
        $IOMock = $this->getMockBuilder(IOInterface::class)
            ->getMock();

        $IOMock->expects($this->once())
            ->method('write')
            ->with($this->equalTo($message));


        $method = new Method('write');
        $method->addParameter(0, $message);

        $module = new IOModule();
        $module->setIO($IOMock);
        $module->runMethod($method, new MixedCollection());
    }

    public function testMethodWriteMustWriteSomeMessagesToTheOutput(): void
    {
        $message1 = 'Hi Mr. Robot';
        $message2 = 'Elliot Alderson';
        $IOMock = $this->getMockBuilder(IOInterface::class)
            ->getMock();

        $IOMock->expects($this->exactly(2))
            ->method('write')
            ->withConsecutive(
                [$this->equalTo($message1)],
                [$this->equalTo($message2)]
            );


        $method = new Method('write');
        $method->addParameter(0, $message1)
            ->addParameter(1, $message2);

        $module = new IOModule();
        $module->setIO($IOMock);
        $module->runMethod($method, new MixedCollection());
    }

    public function testAskMustReturnUserResponse(): void
    {
        $question = 'Who are you?';
        $userResponse = 'Mr. Robot';

        $IOMock = $this->getMockBuilder(IOInterface::class)
            ->getMock();

        $IOMock->expects($this->once())
            ->method('ask')
            ->with($this->equalTo($question))
            ->willReturn($userResponse);

        $method = new Method('ask');
        $method->addParameter('question', $question);

        $module = new IOModule();
        $module->setIO($IOMock);
        $response = $module->runMethod($method, new MixedCollection());
        $arrayResponse = \json_decode($response->getJsonResult(), true);

        $this->assertEquals([
            'response' => $userResponse,
        ], $arrayResponse);
    }

    public function testAskMustReturnDefaultValue(): void
    {
        $question = 'Who are you?';
        $default = 'Mr. Robot';

        $IOMock = $this->getMockBuilder(IOInterface::class)
            ->getMock();

        $IOMock->expects($this->once())
            ->method('ask')
            ->with($this->equalTo($question), $this->equalTo($default))
            ->willReturn($default);

        $method = new Method('ask');
        $method->addParameter('question', $question)
            ->addParameter('default', $default);

        $module = new IOModule();
        $module->setIO($IOMock);
        $response = $module->runMethod($method, new MixedCollection());
        $arrayResponse = \json_decode($response->getJsonResult(), true);

        $this->assertEquals([
            'response' => $default,
        ], $arrayResponse);
    }

    public function testAskMustReturnEmptyValueWhenThereIsNoDefaultValue(): void
    {
        $question = 'Who are you?';

        $IOMock = $this->getMockBuilder(IOInterface::class)
            ->getMock();

        $IOMock->expects($this->once())
            ->method('ask')
            ->with($this->equalTo($question), $this->equalTo(''));

        $method = new Method('ask');
        $method->addParameter('question', $question);

        $module = new IOModule();
        $module->setIO($IOMock);
        $response = $module->runMethod($method, new MixedCollection());
        $arrayResponse = \json_decode($response->getJsonResult(), true);

        $this->assertEquals([
            'response' => '',
        ], $arrayResponse);
    }

    /**
    * @expectedException InvalidArgumentException
    * @expectedExceptionMessage Method "ask" only support 1 or 2 parameters.
    */
    public function testAskMustFailWhenThereAreZeroMethodParameters(): void
    {
        $IOMock = $this->getMockBuilder(IOInterface::class)
            ->getMock();

        $method = new Method('ask');

        $module = new IOModule();
        $module->setIO($IOMock);
        $module->runMethod($method, new MixedCollection());
    }

    /**
    * @expectedException InvalidArgumentException
    * @expectedExceptionMessage Method "ask" only support 1 or 2 parameters.
    */
    public function testAskMustFailWhenThereAreMoreThanTwoMethodParameters(): void
    {
        $question = 'Who are you?';

        $IOMock = $this->getMockBuilder(IOInterface::class)
            ->getMock();

        $method = new Method('ask');
        $method->addParameter('question', $question)
            ->addParameter('default', '')
            ->addParameter('extra', 'fool');

        $module = new IOModule();
        $module->setIO($IOMock);
        $module->runMethod($method, new MixedCollection());
    }

    public function testAskYesNoMustReturnTrueWhenRespondingTrue(): void
    {
        $question = 'Are you happy?';
        $userResponse = true;

        $IOMock = $this->getMockBuilder(IOInterface::class)
            ->getMock();

        $IOMock->expects($this->once())
            ->method('askConfirmation')
            ->with($this->equalTo($question))
            ->willReturn($userResponse);

        $method = new Method('ask_yes_no');
        $method->addParameter('question', $question);

        $module = new IOModule();
        $module->setIO($IOMock);
        $response = $module->runMethod($method, new MixedCollection());
        $arrayResponse = \json_decode($response->getJsonResult(), true);

        $this->assertEquals([
            'response' => $userResponse,
        ], $arrayResponse);
    }

    public function testAskYesNoMustReturnFalseWhenRespondingFalse(): void
    {
        $question = 'Are you happy?';
        $userResponse = false;

        $IOMock = $this->getMockBuilder(IOInterface::class)
            ->getMock();

        $IOMock->expects($this->once())
            ->method('askConfirmation')
            ->with($this->equalTo($question))
            ->willReturn($userResponse);

        $method = new Method('ask_yes_no');
        $method->addParameter('question', $question);

        $module = new IOModule();
        $module->setIO($IOMock);
        $response = $module->runMethod($method, new MixedCollection());
        $arrayResponse = \json_decode($response->getJsonResult(), true);

        $this->assertEquals([
            'response' => $userResponse,
        ], $arrayResponse);
    }

    /**
     * @param mixed $default
     * @param bool $expected
     *
     * @testWith    [true, true]
     *              ["true", true]
     *              ["yes", true]
     *              ["1", true]
     *              [1, true]
     *              [false, false]
     *              ["false", false]
     *              ["no", false]
     *              ["0", false]
     *              [0, false]
     */
    public function testAskYesNoMustReturnTheDefaultValueWhenDefaultIsSomethingTrue($default, bool $expected): void
    {
        $question = 'Are you happy?';

        $IOMock = $this->getMockBuilder(IOInterface::class)
            ->getMock();

        $IOMock->expects($this->once())
            ->method('askConfirmation')
            ->with($this->equalTo($question), $this->equalTo($expected));

        $method = new Method('ask_yes_no');
        $method->addParameter('question', $question)
            ->addParameter('default', $default);

        $module = new IOModule();
        $module->setIO($IOMock);
        $module->runMethod($method, new MixedCollection());
    }

    /**
    * @expectedException InvalidArgumentException
    * @expectedExceptionMessage Method "ask_yes_no" only support boolean values as default parameter.
    */
    public function testAskYesNoMustFailWhenDefaultIsNotSomethingBoolean(): void
    {
        $question = 'Are you happy?';
        $badDefault = 'no-boolean';

        $IOMock = $this->getMockBuilder(IOInterface::class)
            ->getMock();

        $method = new Method('ask_yes_no');
        $method->addParameter('question', $question)
            ->addParameter('default', $badDefault);

        $module = new IOModule();
        $module->setIO($IOMock);
        $module->runMethod($method, new MixedCollection());
    }

    /**
    * @expectedException InvalidArgumentException
    * @expectedExceptionMessage Method "ask_yes_no" only support 1 or 2 parameters.
    */
    public function testAskYesNoMustFailWhenThereAreZeroMethodParameters(): void
    {
        $IOMock = $this->getMockBuilder(IOInterface::class)
            ->getMock();

        $method = new Method('ask_yes_no');

        $module = new IOModule();
        $module->setIO($IOMock);
        $module->runMethod($method, new MixedCollection());
    }

    /**
    * @expectedException InvalidArgumentException
    * @expectedExceptionMessage Method "ask_yes_no" only support 1 or 2 parameters.
    */
    public function testAskYesNoMustFailWhenThereAreMoreThanTwoMethodParameters(): void
    {
        $question = 'Are you happy?';

        $IOMock = $this->getMockBuilder(IOInterface::class)
            ->getMock();

        $method = new Method('ask_yes_no');
        $method->addParameter('question', $question)
            ->addParameter('default', true)
            ->addParameter('extra', 'fool');

        $module = new IOModule();
        $module->setIO($IOMock);
        $module->runMethod($method, new MixedCollection());
    }
}
