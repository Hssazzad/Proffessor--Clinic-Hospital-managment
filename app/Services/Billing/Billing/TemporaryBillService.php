<?php

namespace App\Services\Billing;

use App\Repositories\Billing\Contracts\TemporaryBillRepositoryInterface;

class TemporaryBillService
{
    protected $repository;

    public function __construct(TemporaryBillRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function addTemporaryItem(array $data): array
    {
        try {
            $tmpId = $this->repository->create($data);

            return ['success' => true, 'tmp_id' => $tmpId];
        } catch (\Throwable $e) {
            \Log::error('Add Temporary Item Failed', $data);
            return ['success' => false, 'message' => 'Failed to add temporary item.'];
        }
    }

    public function getTemporaryItems(?string $patientCode): array
    {
        return $this->repository->findByPatientCode($patientCode);
    }

    public function removeTemporaryItem(int $tmpId): bool
    {
        return $this->repository->deleteById($tmpId);
    }

    public function clearTemporaryItems(?string $patientCode): bool
    {
        return $this->repository->deleteByPatientCode($patientCode);
    }
}