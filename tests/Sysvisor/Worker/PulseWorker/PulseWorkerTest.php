<?php
/*
 * This file is part of the sysvisor-workers package.
 *
 * (c) schrobak <schrobak@example.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sysvisor\Test\Worker\PulseWorker;
use Sysvisor\Worker\PulseWorker\PulseWorker;

/**
 * Class PulseWorkerTest
 *
 * @author schrobak <schrobak@example.com>
 * @package Sysvisor\Test\Worker\PulseWorker
 */
class PulseWorkerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function execute()
    {
        $config = [];

        $worker = new PulseWorker($config);
        $response = $worker->execute();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertTrue($response->isContentType('application/json'));
    }
} 