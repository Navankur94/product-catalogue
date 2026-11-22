<?php
namespace App\Http\Controllers\Api;
use App\Mail\ProductEnquiry;
use App\Services\CatalogueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Routing\Controller;
class EnquiryController extends Controller
{
    public function __construct(private CatalogueService $catalogue) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required','integer','min:1'],
            'name' => ['required','string','min:2','max:100'],
            'email' => ['required','email:rfc','max:254'],
            'phone' => ['nullable','string','max:30'],
            'message' => ['nullable','string','max:2000'],
        ]);
        try {
            $product = $this->catalogue->find((int) $data['product_id']);
            if (!$product) return response()->json(['message' => 'Product not found.'], 404);
            $data['product_title'] = (string) $product['title'];
            Mail::to(config('mail.sales_inbox', env('SALES_INBOX', 'sales@example.test')))->send(new ProductEnquiry($data));
            return response()->json(['message' => 'Thank you. Your enquiry has been submitted.'], 201);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'We could not submit your enquiry right now. Please try again.'], 503);
        }
    }
}
