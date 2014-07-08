<?php

class CommandTest extends PHPUnit_Framework_TestCase {

    private $options;

    public function setUp() {
        $o1 = new \Phi\CommandOption('ah');
        $o1->setAliases(array('a'))->setDefault(3);
        $o2 = new \Phi\CommandOption();
        // useless for positional option
        $o2->setAliases(array('b'))->setBoolean();
        $o3 = new \Phi\CommandOption('cool');
        $o3->setAliases(array('c', 'co'))->setBoolean();
        $o4 = new \Phi\CommandOption();
        $o4->setDefault(4);
        $this->options = array($o1, $o2, $o3, $o4);
    }

    public function testOptionSetGet() {
        $cmd = new \Phi\Command(array());
        $this->assertEquals(array(), $cmd->getOptions());
        $this->assertFalse($cmd->hasOption('a'));
        $cmd = new \Phi\Command($this->options);
        $this->assertTrue($cmd->hasOption('a'));
        $this->assertTrue($cmd->hasOption('ah'));
        $this->assertFalse($cmd->hasOption('b'));
        $this->assertTrue($cmd->hasOption('cool'));
        $this->assertTrue($cmd->hasOption('c'));
        $this->assertTrue($cmd->hasOption('co'));
    }

    public function testAlias() {
        $cmd = new \Phi\Command($this->options);
        $this->assertEquals($cmd->getOption('a'), $cmd->getOption('ah'));
        $this->assertEquals($cmd->getOption('c'), $cmd->getOption('co'));
        $this->assertEquals($cmd->getOption('c'), $cmd->getOption('cool'));
    }

    public function testFlag() {
        $cmd = new \Phi\Command($this->options);
        $cmd->setTokens(array('app', '1', '-c', '-a', '1'));
        $this->assertEquals(array('ah' => '1', 'cool' => true), $cmd->getFlags());
        $cmd->setTokens(array('app', '1', '-a', 1, '-cool'));
        $this->assertEquals(array('ah' => '1', 'cool' => true), $cmd->getFlags());
        $cmd->setTokens(array('app', '-a', 1, '-cool'));
        $this->assertEquals(array('ah' => '1', 'cool' => true), $cmd->getFlags());
        $cmd->setTokens(array('app', '-cool'));
        $this->assertEquals(array('ah' => 3, 'cool' => true), $cmd->getFlags());
        $cmd->setTokens(array('app'));
        $this->assertEquals(array('ah' => 3), $cmd->getFlags());
    }

    public function testArgument() {
        $cmd = new \Phi\Command($this->options);
        $cmd->setTokens(array('app', '1', '2'));
        $this->assertEquals(array(1, 2), $cmd->getArguments());
        $cmd->setTokens(array('app', '1'));
        $this->assertEquals(array(1, 4), $cmd->getArguments());
        $cmd->setTokens(array('app'));
        $this->assertEquals(array('1' => 4), $cmd->getArguments());
    }

    /**
     * @expectedException Exception
     */
    public function testValidator() {
        $o = new \Phi\CommandOption('k');
        $o->setValidator('is_numeric', 'err');
        $cmd = new \Phi\Command(array($o));
        $cmd->setTokens(array('app', '-k', 'abc'));
        $cmd->getFlags();
    }

    /**
     * @expectedException Exception
     */
    public function testRequired() {
        $o = new \Phi\CommandOption('k');
        $o->setRequired();
        $cmd = new \Phi\Command(array($o));
        $cmd->setTokens(array('app'));
        $cmd->getFlags();
    }

    /**
     * @expectedException   Exception
     * @dataProvider        illegalFlagProvider
     */
    public function testIllegalFlag($tokens) {
        $cmd = new \Phi\Command($this->options);
        $cmd->setTokens($tokens);
        $flag = $cmd->getFlags();
    }

    /**
     * @expectedException   Exception
     * @dataProvider        illegalArgumentProvider
     */
    public function testIllegalArgument($tokens) {
        $cmd = new \Phi\Command($this->options);
        $cmd->setTokens($tokens);
        $flag = $cmd->getArguments();
    }

    public function illegalArgumentProvider() {
         return array(
            array(array('app', 1, 2, 3, 4)),
            array(array('app', 1, 2, 3)),
        );
    }

    public function illegalFlagProvider() {
         return array(
            array(array('app', '-k', '3')),
            array(array('app', '-a', '4', '-k', '3')),
            array(array('app', '-a', '4', '-col', '3')),
        );
    }


}


