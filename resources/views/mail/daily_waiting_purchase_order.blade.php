<x-mail::message>
# Daily Reminder: Waiting Purchase Orders

<x-mail::panel>
This email is a daily reminder of pending purchase orders. Please review and take necessary action.
</x-mail::panel>

<x-mail::table>
| Mold Name       | Process         | Price         | Quotation No    | Remark       | Photo Preview  |
| --------------- | --------------- | ------------- | --------------- | ------------ | --------------- |
@foreach ($orders as $order)
| {{ $order->mold_name }} | {{ $order->process }} | Rp. {{ number_format($order->price, 2) }} | {{ $order->quotation_no }} | {{ $order->remark ?? '-' }} | <img src="{{ asset('storage/uploads/' . $order->capture_photo_path) }}" alt="Preview" style="height: 50px; width: auto; border-radius: 5px;"> |
@endforeach
</x-mail::table>

<x-mail::button :url="$url">
View More Detail
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
