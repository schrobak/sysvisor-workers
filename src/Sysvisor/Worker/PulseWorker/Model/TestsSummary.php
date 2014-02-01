<?php
/*
 * This file is part of the SysVisor Workers package.
 *
 * (c) Sławomir Chrobak <slawomir.chrobak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sysvisor\Worker\PulseWorker\Model;

use Sysvisor\Worker\PulseWorker\Converter\Convertable;

/**
 * Class TestsSummary
 *
 * @author Sławomir Chrobak <slawomir.chrobak@gmail.com>
 * @package Sysvisor\Worker\PulseWorker\Model
 */
final class TestsSummary implements Convertable
{
    /**
     * @var int
     */
    private $total;

    /**
     * @var int
     */
    private $errors;

    /**
     * @var int
     */
    private $failures;

    /**
     * @var int
     */
    private $expectedFailures;

    /**
     * @var int
     */
    private $passed;

    /**
     * @var int
     */
    private $skipped;

    /**
     * @param int $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * @param int $expectedFailures
     */
    public function setExpectedFailures($expectedFailures)
    {
        $this->expectedFailures = $expectedFailures;
    }

    /**
     * @param int $failures
     */
    public function setFailures($failures)
    {
        $this->failures = $failures;
    }

    /**
     * @param int $passed
     */
    public function setPassed($passed)
    {
        $this->passed = $passed;
    }

    /**
     * @param int $skipped
     */
    public function setSkipped($skipped)
    {
        $this->skipped = $skipped;
    }

    /**
     * @param int $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }
}