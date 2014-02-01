<?php
/*
 * This file is part of the SysVisor Workers package.
 *
 * (c) Sławomir Chrobak <slawomir.chrobak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sysvisor\Worker\PulseWorker\Converter;

/**
 * Interface ConverterInterface
 *
 * @author Sławomir Chrobak <slawomir.chrobak@gmail.com>
 * @package Sysvisor\Worker\PulseWorker\Converter
 */
interface ConverterInterface
{
    /**
     * @param Convertable $object
     * @return mixed
     */
    public function convert(Convertable $object);
} 