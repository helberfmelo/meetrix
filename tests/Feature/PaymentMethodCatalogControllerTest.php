<?php

namespace Tests\Feature;

use App\Models\ConnectedAccount;
use App\Models\SchedulingPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodCatalogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_returns_card_and_pix_for_brl_when_pix_is_enabled(): void
    {
        config()->set('payments.checkout.payment_methods_by_currency', [
            'BRL' => ['card', 'pix'],
            'USD' => ['card'],
        ]);
        config()->set('payments.checkout.pix.enabled', true);
        config()->set('payments.checkout.pix.split_enabled', false);
        config()->set('payments.checkout.pix.requires_payments_mode', true);

        $merchant = User::factory()->create([
            'account_mode' => 'scheduling_with_payments',
            'currency' => 'BRL',
        ]);

        $page = SchedulingPage::create([
            'user_id' => $merchant->id,
            'slug' => 'catalog-brl-pix',
            'title' => 'Catalog BRL PIX',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/payments/methods?currency=BRL&scheduling_page_id=' . $page->id);

        $response->assertOk()
            ->assertJsonPath('currency', 'BRL')
            ->assertJsonPath('stripe_payment_method_types.0', 'card')
            ->assertJsonPath('stripe_payment_method_types.1', 'pix')
            ->assertJsonPath('flags.pix_enabled', true);
    }

    public function test_public_catalog_returns_card_only_when_pix_is_disabled(): void
    {
        config()->set('payments.checkout.payment_methods_by_currency', [
            'BRL' => ['card', 'pix'],
            'USD' => ['card'],
        ]);
        config()->set('payments.checkout.pix.enabled', false);
        config()->set('payments.checkout.pix.split_enabled', false);
        config()->set('payments.checkout.pix.requires_payments_mode', true);

        $merchant = User::factory()->create([
            'account_mode' => 'scheduling_with_payments',
            'currency' => 'BRL',
        ]);

        $page = SchedulingPage::create([
            'user_id' => $merchant->id,
            'slug' => 'catalog-brl-card',
            'title' => 'Catalog BRL Card',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/payments/methods?currency=BRL&scheduling_page_id=' . $page->id);

        $response->assertOk()
            ->assertJsonPath('currency', 'BRL')
            ->assertJsonPath('stripe_payment_method_types.0', 'card')
            ->assertJsonCount(1, 'stripe_payment_method_types')
            ->assertJsonPath('flags.pix_enabled', false);
    }

    public function test_public_catalog_blocks_pix_when_split_is_active_and_pix_split_is_disabled(): void
    {
        config()->set('payments.checkout.payment_methods_by_currency', [
            'BRL' => ['card', 'pix'],
        ]);
        config()->set('payments.checkout.pix.enabled', true);
        config()->set('payments.checkout.pix.split_enabled', false);
        config()->set('payments.checkout.pix.requires_payments_mode', true);

        $merchant = User::factory()->create([
            'account_mode' => 'scheduling_with_payments',
            'currency' => 'BRL',
        ]);

        ConnectedAccount::create([
            'user_id' => $merchant->id,
            'provider' => 'stripe_connect',
            'provider_account_id' => 'acct_catalog_1',
            'status' => 'active',
            'charges_enabled' => true,
            'payouts_enabled' => true,
            'details_submitted' => true,
        ]);

        $page = SchedulingPage::create([
            'user_id' => $merchant->id,
            'slug' => 'catalog-brl-split',
            'title' => 'Catalog BRL Split',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/payments/methods?currency=BRL&scheduling_page_id=' . $page->id);

        $response->assertOk()
            ->assertJsonPath('currency', 'BRL')
            ->assertJsonPath('stripe_payment_method_types.0', 'card')
            ->assertJsonCount(1, 'stripe_payment_method_types')
            ->assertJsonPath('flags.pix_enabled', false);
    }

    public function test_public_catalog_for_subscription_context_blocks_pix_even_when_enabled(): void
    {
        config()->set('payments.checkout.payment_methods_by_currency', [
            'BRL' => ['card', 'pix'],
        ]);
        config()->set('payments.checkout.pix.enabled', true);
        config()->set('payments.checkout.pix.split_enabled', true);
        config()->set('payments.checkout.pix.requires_payments_mode', true);

        $response = $this->getJson('/api/payments/methods?currency=BRL&context=subscription');

        $response->assertOk()
            ->assertJsonPath('context', 'subscription')
            ->assertJsonPath('currency', 'BRL')
            ->assertJsonPath('stripe_payment_method_types.0', 'card')
            ->assertJsonCount(1, 'stripe_payment_method_types')
            ->assertJsonPath('flags.pix_enabled', false);
    }
}
