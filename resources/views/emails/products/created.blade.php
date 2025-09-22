@component('mail::message')
# New product has been created

Honey {{ $notifiable->name }}:

A new product has been created:

**Product Information:**
- name:{{ $product->name }}
- serial number:{{ $product->sku }}
- type:{{ $product->type }}
- brand:{{ $product->brand }}
- Creation time:{{ $product->created_at->format('Y-m-d H:i:s') }}

@component('mail::button', ['url' => route('products.show', $product)])
View product details
@endcomponent

If you have any questions, please contact the relevant person in charge in time.

Thanks!<br>
{{ config('app.name') }}
@endcomponent 