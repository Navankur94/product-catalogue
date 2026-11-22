<h2>New product enquiry</h2>
<p><strong>Product:</strong> {{ $product->title }} (ID: {{ $product->id }})</p>
<p><strong>Price:</strong> {{ number_format($product->price, 2) }}</p>
<p><strong>Name:</strong> {{ $customerName }}</p>
<p><strong>Email:</strong> {{ $customerEmail }}</p>
<p><strong>Phone:</strong> {{ $customerPhone ?? 'Not provided' }}</p>
<p><strong>Message:</strong></p>
<p>{!! nl2br(e($customerMessage ?? 'No message provided.')) !!}</p>
