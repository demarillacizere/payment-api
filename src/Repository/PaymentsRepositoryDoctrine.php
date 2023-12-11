<?php

namespace PaymentApi\Repository;

use PaymentApi\Model\Payments;
use Doctrine\ORM\Exception\NotSupported;

/**
 * PaymentsRepositoryDoctrine
 */
class PaymentsRepositoryDoctrine extends A_Repository implements PaymentsRepository
{
   
    /**
     * @throws NotSupported
     */
    public function findAll(): array
    {
        return $this->em->getRepository(Payments::class)->findAll();
    }

    /**
     * @throws NotSupported
     */
    public function findById(int $methodId): Payments|null
    {
        return $this->em->getRepository(Payments::class)->find($methodId);
    }
}