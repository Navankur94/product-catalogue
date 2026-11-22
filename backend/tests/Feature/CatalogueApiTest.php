<?php
namespace Tests\Feature;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProductEnquiry;
use Tests\TestCase;
class CatalogueApiTest extends TestCase
{
    protected function setUp(): void { parent::setUp(); Cache::flush(); config(['services.fake_store.base_url' => 'https://fakestoreapi.com']); }
    private function fakeProducts(): array
    {
        return [
          ['id'=>1,'title'=>'Alpha phone','description'=>'Smart phone','category'=>'electronics','price'=>10,'image'=>'https://example.test/a.png','rating'=>['rate'=>4.9,'count'=>10]],
          ['id'=>2,'title'=>'Beta shoe','description'=>'Running shoe','category'=>"men's clothing",'price'=>20,'image'=>'https://example.test/b.png','rating'=>['rate'=>4.2,'count'=>8]],
          ['id'=>3,'title'=>'Gamma phone case','description'=>'Protective case','category'=>'electronics','price'=>5,'image'=>'https://example.test/c.png','rating'=>['rate'=>3.8,'count'=>5]],
        ];
    }
    public function test_health_endpoint_is_available(): void { $this->getJson('/api/health')->assertOk()->assertJson(['status'=>'ok']); }
    public function test_search_filters_products_on_backend(): void
    {
        Http::fake(['https://fakestoreapi.com/products' => Http::response($this->fakeProducts())]);
        $this->getJson('/api/products?q=phone')->assertOk()->assertJsonCount(2, 'data');
    }
    public function test_category_returns_featured_and_all_category_products(): void
    {
        Http::fake(['https://fakestoreapi.com/products' => Http::response($this->fakeProducts())]);
        $this->getJson('/api/categories/electronics/products')->assertOk()->assertJsonCount(2, 'products')->assertJsonCount(2, 'featured');
    }
    public function test_enquiry_validates_and_sends_sales_email(): void
    {
        Mail::fake();
        Http::fake(['https://fakestoreapi.com/products' => Http::response($this->fakeProducts())]);
        $this->postJson('/api/enquiries', ['product_id'=>1,'product_title'=>'Client supplied spoof','name'=>'Alex Customer','email'=>'alex@example.com','phone'=>'1234567890','message'=>'Please contact me'])->assertCreated()->assertJsonStructure(['message']);
        Mail::assertSent(ProductEnquiry::class, fn ($mail) => $mail->hasTo('sales@example.test') && $mail->enquiry['product_title'] === 'Alpha phone');
    }
    public function test_enquiry_rejects_invalid_email(): void
    {
        $this->postJson('/api/enquiries', ['product_id'=>1,'name'=>'Alex Customer','email'=>'not-an-email'])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }
    public function test_featured_endpoint_returns_five_highest_rated_products(): void
    {
        $products = $this->fakeProducts();
        for ($id = 4; $id <= 7; $id++) $products[] = ['id'=>$id,'title'=>'Item '.$id,'description'=>'Test','category'=>'electronics','price'=>1,'image'=>'https://example.test/x.png','rating'=>['rate'=>4 + ($id / 10),'count'=>1]];
        Http::fake(['https://fakestoreapi.com/products' => Http::response($products)]);
        $response = $this->getJson('/api/products/featured')->assertOk()->assertJsonCount(5, 'data');
        $this->assertSame(7, $response->json('data.0.id'));
    }
    public function test_unknown_product_returns_404(): void
    {
        Http::fake(['https://fakestoreapi.com/products' => Http::response($this->fakeProducts())]);
        $this->getJson('/api/products/999')->assertNotFound();
    }
}
