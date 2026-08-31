<?php

namespace Packages\Newsletter\Src\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Packages\Newsletter\Src\Models\NewsletterSubscriber;
use Packages\Newsletter\Src\Repositories\Interfaces\NewsletterRepositoryInterface;

class NewsletterRepository implements NewsletterRepositoryInterface
{
    public function findByEmail(string $email): ?NewsletterSubscriber
    {
        return NewsletterSubscriber::where('email', $email)->first();
    }

    public function findByToken(string $token): ?NewsletterSubscriber
    {
        return NewsletterSubscriber::where('opt_in_token', $token)->first();
    }

    public function paginateForAdmin(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        $query = NewsletterSubscriber::query();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['locale'])) {
            $query->where('locale', $filters['locale']);
        }

        if (! empty($filters['search'])) {
            $query->where('email', 'like', '%'.$filters['search'].'%');
        }

        return $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();
    }
}
