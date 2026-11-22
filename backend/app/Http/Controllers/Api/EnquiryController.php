<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreEnquiryRequest;
use App\Mail\ProductEnquiry;
use App\Services\CatalogueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

final class EnquiryController extends Controller
{
    public function store(StoreEnquiryRequest $request, CatalogueService $catalogue): JsonResponse
    {
        $data = $request->validated();

        // Product details come from the provider, never from the client.
        $product = $catalogue->find((int) $data['product_id']);
        if ($product === null) {
            throw ValidationException::withMessages(['product_id' => 'The selected product does not exist.']);
        }

        try {
            Mail::to((string) config('mail.sales_inbox'))->send(new ProductEnquiry(
                product: $product,
                customerName: $data['name'],
                customerEmail: $data['email'],
                customerPhone: $data['phone'] ?? null,
                customerMessage: $data['message'] ?? null,
            ));
        } catch (TransportExceptionInterface $e) {
            report($e);

            return response()->json(['message' => 'We could not send your enquiry right now. Please try again later.'], 503);
        }

        return response()->json(['message' => 'Thank you for your enquiry. Our sales team will be in touch soon.'], 201);
    }
}
