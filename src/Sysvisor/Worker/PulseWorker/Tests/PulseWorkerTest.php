<?php

namespace Sysvisor\Worker\PulseWorker\Tests;

use Sysvisor\Worker\PulseWorker\PulseWorker;

class PulseWorkerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Zend\Uri\Exception\InvalidUriException
     */
    public function parseWithInvalidUri()
    {
        new PulseWorker('invalid');

        $this->fail('URI has valid format.');
    }

    /**
     * @test
     */
    public function runWorker()
    {
        $worker = new PulseWorker('http://schrobak:xdev1234@pulse.int.x-formation.com/xmlrpc');
        $worker->execute();
        $this->markTestIncomplete('Missing assertion');
    }
}