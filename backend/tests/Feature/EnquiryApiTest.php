<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\ProductProvider;
use App\Exceptions\ProductProviderUnavailable;
use App\Mail\ProductEnquiry;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\InMemoryProductProvider;
use Tests\TestCase;

final class EnquiryApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(ProductProvider::class, new InMemoryProductProvider([
            InMemoryProductProvider::product(1, title: 'Alpha phone'),
        ]));
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'product_id' => 1,
            'name' => 'Alex Customer',
            'email' => 'alex@example.com',
            'phone' => '+44 (0)20 7946-0000',
            'message' => "Is this in stock?\nThanks",
        ], $overrides);
    }

    public function test_valid_enquiry_sends_sales_email_and_returns_thank_you(): void
    {
        Mail::fake();

        $this->postJson('/api/enquiries', $this->payload(['product_title' => 'Client-supplied spoof']))
            ->assertCreated()
            ->assertExactJson(['message' => 'Thank you for your enquiry. Our sales team will be in touch soon.']);

        Mail::assertSent(ProductEnquiry::class, function (ProductEnquiry $mail): bool {
            return $mail->hasTo('sales@example.test')
                && $mail->hasReplyTo('alex@example.com', 'Alex Customer')
                && $mail->hasSubject('Product enquiry: Alpha phone')
                && $mail->product->title === 'Alpha phone'
                && $mail->customerPhone === '+44 (0)20 7946-0000';
        });
        Mail::assertSentCount(1);
    }

    public function test_sales_inbox_is_configurable(): void
    {
        Mail::fake();
        config(['mail.sales_inbox' => 'orders@shop.test']);

        $this->postJson('/api/enquiries', $this->payload())->assertCreated();

        Mail::assertSent(ProductEnquiry::class, fn (ProductEnquiry $mail): bool => $mail->hasTo('orders@shop.test'));
    }

    public function test_optional_fields_may_be_omitted(): void
    {
        Mail::fake();

        $this->postJson('/api/enquiries', ['product_id' => 1, 'name' => 'Al', 'email' => 'al@example.com', 'phone' => ''])
            ->assertCreated();

        Mail::assertSent(ProductEnquiry::class, fn (ProductEnquiry $mail): bool => $mail->customerPhone === null && $mail->customerMessage === null);
    }

    public function test_email_renders_escaped_customer_content(): void
    {
        $mail = new ProductEnquiry(
            InMemoryProductProvider::product(1, title: 'Alpha phone'),
            'Alex <script>',
            'alex@example.com',
            null,
            '<b>hi</b>',
        );

        $mail->assertSeeInHtml('Alpha phone');
        $mail->assertSeeInHtml('Alex &lt;script&gt;', false);
        $mail->assertDontSeeInHtml('<b>hi</b>', false);
        $mail->assertSeeInHtml('Not provided');
    }

    public function test_missing_required_fields_return_422(): void
    {
        Mail::fake();

        $this->postJson('/api/enquiries', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['product_id', 'name', 'email']);

        Mail::assertNothingSent();
    }

    /** @return array<string, array{array<string, mixed>, string}> */
    public static function invalidPayloads(): array
    {
        return [
            'invalid email' => [['email' => 'not-an-email'], 'email'],
            'name too short' => [['name' => 'A'], 'name'],
            'name too long' => [['name' => str_repeat('a', 101)], 'name'],
            'phone with letters' => [['phone' => 'call me maybe'], 'phone'],
            'message too long' => [['message' => str_repeat('a', 2001)], 'message'],
            'non-integer product id' => [['product_id' => 'abc'], 'product_id'],
            'unknown product' => [['product_id' => 999], 'product_id'],
        ];
    }

    #[DataProvider('invalidPayloads')]
    public function test_invalid_input_returns_422(array $overrides, string $field): void
    {
        Mail::fake();

        $this->postJson('/api/enquiries', $this->payload($overrides))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field])
            ->assertJsonStructure(['message', 'errors']);

        Mail::assertNothingSent();
    }

    public function test_mail_transport_failure_returns_503(): void
    {
        // Real SMTP transport pointed at a closed local port: connection is refused.
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1, 'mail.mailers.smtp.timeout' => 2]);

        $this->postJson('/api/enquiries', $this->payload())
            ->assertStatus(503)
            ->assertExactJson(['message' => 'We could not send your enquiry right now. Please try again later.']);
    }

    public function test_provider_failure_returns_503(): void
    {
        Mail::fake();
        $this->app->instance(ProductProvider::class, new class implements ProductProvider
        {
            public function all(): array
            {
                throw new ProductProviderUnavailable('down');
            }

            public function find(int $id): ?\App\Data\Product
            {
                throw new ProductProviderUnavailable('down');
            }
        });

        $this->postJson('/api/enquiries', $this->payload())->assertStatus(503);
        Mail::assertNothingSent();
    }

    public function test_enquiries_are_rate_limited_per_client(): void
    {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/enquiries', $this->payload())->assertCreated();
        }

        $this->postJson('/api/enquiries', $this->payload())
            ->assertStatus(429)
            ->assertJsonStructure(['message'])
            ->assertHeader('Retry-After');
    }

    public function test_default_log_mailer_writes_email_without_any_account(): void
    {
        config(['mail.default' => 'log']);
        $logged = [];
        Log::listen(function ($event) use (&$logged): void {
            $logged[] = (string) $event->message;
        });

        $this->postJson('/api/enquiries', $this->payload())->assertCreated();

        $this->assertTrue(collect($logged)->contains(fn (string $m): bool => str_contains($m, 'Product enquiry: Alpha phone')));
    }
}
