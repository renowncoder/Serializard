<?php
namespace Thunder\Serializard\Format;

use Thunder\Serializard\Exception\SerializationFailureException;
use Thunder\Serializard\NormalizerContainer\NormalizerContainerInterface as Normalizers;
use Thunder\Serializard\HydratorContainer\HydratorContainerInterface as Hydrators;
use Thunder\Serializard\NormalizerContext\NormalizerContextInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class JsonFormat extends AbstractFormat
{
    public function serialize($var, Normalizers $normalizers, NormalizerContextInterface $context)
    {
        $json = @json_encode($this->doSerialize($var, $normalizers, $context));

        if(json_last_error() !== JSON_ERROR_NONE) {
            throw SerializationFailureException::fromJson(json_last_error_msg());
        }

        return $json;
    }

    public function unserialize($var, $class, Hydrators $hydrators)
    {
        return $this->doUnserialize(json_decode($var, true), $class, $hydrators);
    }
}
