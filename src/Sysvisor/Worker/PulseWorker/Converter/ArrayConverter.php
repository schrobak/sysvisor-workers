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
 * Class ArrayConverter
 *
 * @author Sławomir Chrobak <slawomir.chrobak@gmail.com>
 * @package Sysvisor\Worker\PulseWorker\Converter
 */
class ArrayConverter implements ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert(Convertable $object)
    {
        $reflection = new \ReflectionObject($object);
        $properties = $reflection->getProperties();

        $array = [];
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);

            if ($value instanceof \DateTime) {
                $value = $value->format(\DateTime::ISO8601);
            } elseif ($value instanceof Convertable) {
                $value = $this->convert($value);
            } elseif (is_array($value)) {
                $converted = [];
                foreach ($value as $inside) {
                    $converted[] = $this->convert($inside);
                }
                $value = $converted;
            }

            $array[$property->getName()] = $value;
        }

        return $array;
    }
}