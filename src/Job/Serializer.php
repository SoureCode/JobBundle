<?php

namespace SoureCode\Bundle\Job\Job;

use Psr\Log\LoggerInterface;
use SoureCode\Bundle\Job\Model\EncodedPayload;
use SoureCode\Bundle\Job\Model\Payload;
use stdClass;
use Symfony\Component\Serializer\Context\Encoder\JsonEncoderContextBuilder;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

class Serializer
{
    public function __construct(
        private readonly LoggerInterface     $logger,
        private readonly SerializerInterface $serializer,
    )
    {
    }

    /**
     * @param object $object The object to encode.
     * @param array $context The context for serialization.
     * @return EncodedPayload Returns the encoded payload.
     */
    public function encode(object $object, array $context = []): EncodedPayload
    {
        $contextBuilder = $this->getContextBuilder($context);
        $body = $this->serializer->serialize($object, 'json', $contextBuilder->toArray());

        return new EncodedPayload(get_class($object), $body);
    }

    /**
     * @param EncodedPayload $encodedPayload The encoded payload.
     * @param array $context The context for deserialization.
     * @return Payload Returns the decoded payload.
     */
    public function decode(EncodedPayload $encodedPayload, array $context = []): Payload
    {
        $type = $encodedPayload->getType();

        if (!class_exists($type)) {
            $this->logger->warning('Class {class} does not exist.', [
                'class' => $type,
            ]);

            $type = stdClass::class;
        }

        $contextBuilder = $this->getContextBuilder($context);
        $body = $this->serializer->deserialize($encodedPayload->getBody(), $type, 'json', $contextBuilder->toArray());

        return $encodedPayload->decode($body);
    }

    private function getContextBuilder(array $context): JsonEncoderContextBuilder
    {
        $contextBuilder = (new ObjectNormalizerContextBuilder())->withContext($context);
        $contextBuilder = (new JsonEncoderContextBuilder())->withContext($contextBuilder);

        return $contextBuilder;
    }
}