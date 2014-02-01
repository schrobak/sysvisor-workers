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
 * Class Changelist
 *
 * @author Sławomir Chrobak <slawomir.chrobak@gmail.com>
 * @package Sysvisor\Worker\PulseWorker\Model
 */
final class Changelist implements Convertable
{
    /**
     * @var string
     */
    private $revision;

    /**
     * @var string
     */
    private $author;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var \DateTime
     */
    private $dateTime;

    /**
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @param \DateTime $date
     */
    public function setDateTime(\DateTime $date)
    {
        $this->dateTime = $date;
    }

    /**
     * @param string $revision
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;
    }
}