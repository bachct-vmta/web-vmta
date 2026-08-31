<?php

namespace Packages\Newsletter\Src\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Packages\Newsletter\Src\Models\NewsletterSubscriber;

interface NewsletterRepositoryInterface
{
    public function findByEmail(string $email): ?NewsletterSubscriber;

    public function findByToken(string $token): ?NewsletterSubscriber;

    public function paginateForAdmin(array $filters, int $perPage = 25): LengthAwarePaginator;
}
