<?php

namespace SoureCode\Bundle\Job\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use SoureCode\Bundle\Job\Model\EncodedPayload;

#[ORM\Entity]
class Job
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT, length: 20)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING)]
    private string $identity;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $finishedAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $cancelledAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $failedAt = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $payload = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $result = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $error = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): Job
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeImmutable $startedAt): Job
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeImmutable $finishedAt): Job
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getCancelledAt(): ?\DateTimeImmutable
    {
        return $this->cancelledAt;
    }

    public function setCancelledAt(?\DateTimeImmutable $cancelledAt): Job
    {
        $this->cancelledAt = $cancelledAt;

        return $this;
    }

    public function getFailedAt(): ?\DateTimeImmutable
    {
        return $this->failedAt;
    }

    public function setFailedAt(?\DateTimeImmutable $failedAt): Job
    {
        $this->failedAt = $failedAt;

        return $this;
    }

    public function getEncodedPayload(): EncodedPayload
    {
        return EncodedPayload::fromString($this->payload);
    }

    public function setEncodedPayload(EncodedPayload $payload): Job
    {
        $this->payload = $payload->toString();

        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): Job
    {
        $this->result = $result;

        return $this;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): Job
    {
        $this->error = $error;
        return $this;
    }

    public function isPending(): bool
    {
        return null === $this->startedAt && null === $this->finishedAt && null === $this->cancelledAt && null === $this->failedAt;
    }

    public function isRunning(): bool
    {
        return null !== $this->startedAt && null === $this->finishedAt && null === $this->cancelledAt && null === $this->failedAt;
    }

    public function isFinished(): bool
    {
        return null !== $this->startedAt && null !== $this->finishedAt && null === $this->cancelledAt && null === $this->failedAt;
    }

    public function isCancelled(): bool
    {
        return null !== $this->startedAt && null !== $this->finishedAt && null !== $this->cancelledAt && null === $this->failedAt;
    }

    public function isFailed(): bool
    {
        return null !== $this->startedAt && null === $this->finishedAt && null === $this->cancelledAt && null !== $this->failedAt;
    }

    public function getPayload(): ?string
    {
        return $this->payload;
    }

    public function setPayload(?string $payload): void
    {
        $this->payload = $payload;
    }

    public function getIdentity(): string
    {
        return $this->identity;
    }

    public function setIdentity(string $identity): void
    {
        $this->identity = $identity;
    }
}
