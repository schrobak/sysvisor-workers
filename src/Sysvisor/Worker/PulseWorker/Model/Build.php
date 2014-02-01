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
 * Class Build
 *
 * @author Sławomir Chrobak <slawomir.chrobak@gmail.com>
 * @package Sysvisor\Worker\PulseWorker\Model
 */
final class Build implements Convertable
{
    /**
     * @var int
     */
    private $number;

    /**
     * @var string
     */
    private $projectName;

    /**
     * @var bool
     */
    private $completed;

    /**
     * @var string
     */
    private $status;

    /**
     * @var int
     */
    private $progress;

    /**
     * @var string
     */
    private $revision;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var \DateTime
     */
    private $startedAt;

    /**
     * @var \DateTime
     */
    private $endedAt;

    /**
     * @var array
     */
    private $changelists = [];

    /**
     * @var \Sysvisor\Worker\PulseWorker\Model\TestsSummary
     */
    private $testsSummary = null;

    public function __construct($number)
    {
        $this->number = (int) $number;
    }

    /**
     * @param array $changelists
     */
    public function setChangelists(array $changelists)
    {
        $this->changelists = $changelists;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $projectName
     */
    public function setProjectName($projectName)
    {
        $this->projectName = $projectName;
    }

    /**
     * @return string
     */
    public function getProjectName()
    {
        return $this->projectName;
    }

    /**
     * @param \Sysvisor\Worker\PulseWorker\Model\TestsSummary $testsSummary
     */
    public function setTestsSummary(TestsSummary $testsSummary)
    {
        $this->testsSummary = $testsSummary;
    }

    /**
     * @param bool $completed
     */
    public function setCompleted($completed)
    {
        $this->completed = (bool) $completed;
    }

    /**
     * @param \DateTime $startedAt
     */
    public function setStartedAt(\DateTime $startedAt)
    {
        $this->startedAt = $startedAt;
    }

    /**
     * @param \DateTime $endedAt
     */
    public function setEndedAt(\DateTime $endedAt)
    {
        $this->endedAt = $endedAt;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @param int $progress
     */
    public function setProgress($progress)
    {
        $this->progress = (int) $progress;
    }

    /**
     * @param string $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    /**
     * @param string $revision
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;
    }
}