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
        $this->markTestIncomplete('Change according to long running process implementation.');
        $worker = new PulseWorker('http://schrobak:xdev1234@pulse.int.x-formation.com/xmlrpc');
        $result = $worker->run();

        $this->assertCount(38, $result);
    }
}