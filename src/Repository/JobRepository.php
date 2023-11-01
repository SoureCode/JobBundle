<?php

namespace SoureCode\Bundle\Job\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use SoureCode\Bundle\Job\Entity\Job;
use SoureCode\Bundle\Job\Model\JobIdentity;

/**
 * @extends ServiceEntityRepository<Job>
 *
 * @method Job|null find($id, $lockMode = null, $lockVersion = null)
 * @method Job|null findOneBy(array $criteria, array $orderBy = null)
 * @method Job[]    findAll()
 * @method Job[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Job::class);
    }

    public function save(Job $job): void
    {
        $entityManager = $this->getEntityManager();

        if (!$entityManager->contains($job)) {
            $entityManager->persist($job);
        }

        $entityManager->flush();
    }

    public function remove(Job $job): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->refresh($job);

        if ($job->isRunning()) {
            throw new \InvalidArgumentException(sprintf('Can not remove running job "%s".', $job->getId()));
        }

        $entityManager->remove($job);
        $entityManager->flush();
    }


    /**
     * Returns the identifier of an entity using the given ClassMetadata and entity or identity.
     *
     * @param ClassMetadata $classMetadata The metadata of the entity class.
     * @param object $entityOrIdentity The entity or identity for which to retrieve the identifier.
     *
     * @return string The identifier of the entity as a string, using hyphens to separate multiple identifiers if applicable.
     */
    private function getIdentifier(ClassMetadata $classMetadata, object $entityOrIdentity): string
    {
        $identifiers = $classMetadata->getIdentifierValues($entityOrIdentity);

        return implode('-', array_map(static fn($value) => (string)$value, $identifiers));
    }


    /**
     * Resolves the ClassMetadata for the given entity.
     *
     * @param object $entity The entity for which to resolve the ClassMetadata.
     *
     * @return ClassMetadata The ClassMetadata for the given entity.
     *
     * @throws InvalidArgumentException if the entity is not persisted or scheduled for persistence.
     */
    private function resolveClassMetadata(object $entity): ClassMetadata
    {
        $entityManger = $this->getEntityManager();
        $unitOfWork = $entityManger->getUnitOfWork();

        // TODO: test entity states
        if (!$entityManger->contains($entity) || $unitOfWork->isEntityScheduled($entity)) {
            throw new \InvalidArgumentException(sprintf(
                'Entity "%s" must be persisted before creating an identity',
                $entity::class
            ));
        }

        return $entityManger->getClassMetadata($entity::class);
    }

    /**
     * Generates an identity string for the given entity or identity object.
     *
     * @param object|string $entityOrIdentity The entity or identity object to generate the identity for.
     * @param object $payload The payload object associated with the identity.
     *
     * @return string The generated identity string.
     */
    public function createIdentity(object|string $entityOrIdentity, object $payload): string
    {
        $payloadClass = $payload::class;

        if (is_string($entityOrIdentity)) {
            return implode("-", [
                $payloadClass,
                $entityOrIdentity,
            ]);
        }

        $classMetadata = $this->resolveClassMetadata($entityOrIdentity);
        $identifier = $this->getIdentifier($classMetadata, $entityOrIdentity);

        return implode([
            $payloadClass,
            $classMetadata->getName(),
            $identifier
        ]);
    }

    public function getPending(object|string $entityOrIdentity, object $payload): array
    {
        $identity = $this->createIdentity($entityOrIdentity, $payload);

        return $this->getPendingByIdentity($identity);
    }

    /**
     * Retrieves a list of pending jobs by their identity.
     *
     * @param string $identity The identity of the jobs to retrieve.
     *
     * @return array Returns an array of pending Job objects that match the given identity.
     */
    public function getPendingByIdentity(string $identity): array
    {
        $queryBuilder = $this->createQueryBuilder('job');

        $queryBuilder
            ->where($queryBuilder->expr()->eq('job.identity', ':identity'))
            ->andWhere($queryBuilder->expr()->isNull('job.finishedAt'))
            ->setParameter('identity', $identity);

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    /**
     * Gets all pending items associated with a given Job.
     *
     * @param Job $job The Job object to retrieve pending items for.
     *
     * @return array An array of pending items.
     */
    public function getPendingByJob(Job $job): array
    {
        $jobs = $this->getPendingByIdentity($job->getIdentity());

        return array_filter($jobs, static fn(Job $pendingJob) => $pendingJob->getId() !== $job->getId());
    }
}
