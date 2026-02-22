<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\Payments\CheckoutPaymentMethodService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutPaymentMethodServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_for_booking_includes_pix_for_brl_when_enabled_without_split(): void
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

        $methods = app(CheckoutPaymentMethodService::class)->resolveForBooking($merchant, 'BRL', false, false);

        $this->assertSame(['card', 'pix'], $methods);
    }

    public function test_resolve_for_booking_blocks_pix_when_split_is_active_and_pix_split_is_disabled(): void
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

        $methods = app(CheckoutPaymentMethodService::class)->resolveForBooking($merchant, 'BRL', true, false);

        $this->assertSame(['card'], $methods);
    }

    public function test_resolve_for_booking_blocks_pix_when_manual_capture_is_required(): void
    {
        config()->set('payments.checkout.payment_methods_by_currency', [
            'BRL' => ['card', 'pix'],
        ]);
        config()->set('payments.checkout.pix.enabled', true);
        config()->set('payments.checkout.pix.split_enabled', true);
        config()->set('payments.checkout.pix.requires_payments_mode', true);

        $merchant = User::factory()->create([
            'account_mode' => 'scheduling_with_payments',
            'currency' => 'BRL',
        ]);

        $methods = app(CheckoutPaymentMethodService::class)->resolveForBooking($merchant, 'BRL', false, true);

        $this->assertSame(['card'], $methods);
    }

    public function test_resolve_for_subscription_checkout_blocks_pix_even_when_enabled(): void
    {
        config()->set('payments.checkout.payment_methods_by_currency', [
            'BRL' => ['card', 'pix'],
        ]);
        config()->set('payments.checkout.pix.enabled', true);
        config()->set('payments.checkout.pix.split_enabled', true);
        config()->set('payments.checkout.pix.requires_payments_mode', true);

        $methods = app(CheckoutPaymentMethodService::class)->resolveForSubscriptionCheckout('BRL');

        $this->assertSame(['card'], $methods);
    }

    public function test_resolve_for_booking_blocks_pix_in_production_when_lock_is_enabled(): void
    {
        config()->set('app.env', 'production');
        config()->set('payments.checkout.payment_methods_by_currency', [
            'BRL' => ['card', 'pix'],
        ]);
        config()->set('payments.checkout.pix.enabled', true);
        config()->set('payments.checkout.pix.production_lock', true);
        config()->set('payments.checkout.pix.split_enabled', true);
        config()->set('payments.checkout.pix.requires_payments_mode', true);

        $merchant = User::factory()->create([
            'account_mode' => 'scheduling_with_payments',
            'currency' => 'BRL',
        ]);

        $methods = app(CheckoutPaymentMethodService::class)->resolveForBooking($merchant, 'BRL', false, false);

        $this->assertSame(['card'], $methods);
    }
}
