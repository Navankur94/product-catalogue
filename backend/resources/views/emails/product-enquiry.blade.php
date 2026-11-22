<h2>New product enquiry</h2>
<p><strong>Product:</strong> {{ $enquiry['product_title'] }} (ID: {{ $enquiry['product_id'] }})</p>
<p><strong>Name:</strong> {{ $enquiry['name'] }}</p>
<p><strong>Email:</strong> {{ $enquiry['email'] }}</p>
<p><strong>Phone:</strong> {{ $enquiry['phone'] ?? 'Not provided' }}</p>
<p><strong>Message:</strong></p>
<p>{{ $enquiry['message'] ?? 'No message provided' }}</p>
