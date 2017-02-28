<?php
namespace Thunder\Serializard\Tests;

use Thunder\Serializard\Format\ArrayFormat;
use Thunder\Serializard\Format\JsonFormat;
use Thunder\Serializard\HydratorContainer\FallbackHydratorContainer;
use Thunder\Serializard\NormalizerContainer\FallbackNormalizerContainer;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class FormatTest extends AbstractTestCase
{
    public function testArrayUnserializeInvalidTypeException()
    {
        $format = new ArrayFormat();
        $this->expectException('RuntimeException');
        $format->unserialize(new \stdClass(), 'stdClass', new FallbackHydratorContainer());
    }

    public function testMissingUnserializationHandlerException()
    {
        $format = new ArrayFormat();
        $this->expectException('RuntimeException');
        $format->unserialize(array(), 'stdClass', new FallbackHydratorContainer());
    }

    public function testJsonEncodeSerializationFailureException()
    {
        $format = new JsonFormat();
        $this->expectException('RuntimeException'); // Inf and NaN cannot be JSON encoded
        $format->serialize(INF, new FallbackNormalizerContainer()); // INF is returned as zero on PHP <=5.4
    }
}
