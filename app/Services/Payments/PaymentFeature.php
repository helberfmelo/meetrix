<?php

namespace App\Services\Payments;

use App\Models\User;

class PaymentFeature
{
    public function isGloballyEnabled(): bool
    {
        return (bool) config('payments.enabled', false);
    }

    /**
     * @return array<int, int>
     */
    public function rolloutUserIds(): array
    {
        return array_map('intval', (array) config('payments.rollout_user_ids', []));
    }

    public function isEnabledForUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (($user->account_mode ?? 'scheduling_only') !== 'scheduling_with_payments') {
            return false;
        }

        if ($this->isGloballyEnabled()) {
            return true;
        }

        return in_array((int) $user->id, $this->rolloutUserIds(), true);
    }
}

