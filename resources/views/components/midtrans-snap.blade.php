{{-- Midtrans requires snap.js to load from its own host, and that host must match
     the API PaymentGatewayService got the Snap token from (production vs sandbox). --}}
<script src="{{ \App\Support\ContentSecurityPolicy::midtransAppHost() }}/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
