<!DOCTYPE html>
<html>
<body style="font-family: Arial; font-size:13px;">

<h2>Production Delivery Detail (H+1 – H+3)</h2>

<h1>
    Link untuk melihat Delivery Schedule di Daijo MES: 
    <a href="http://116.254.114.93:8000/deliveryschedule/index" target="_blank">
        Klik di sini
    </a>
</h1>


{{-- ================= FINAL ================= --}}
@if($data->final && $data->final->count())
    <h2 style="margin-top:30px;">FINAL</h2>

    @foreach($data->final as $date => $rows)

        <h3>Delivery Date: {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</h3>

        <table border="1" cellpadding="5" cellspacing="0" width="100%" style="border-collapse: collapse;">
            <thead>
                <tr style="background:#f2f2f2;">
                    <th>SO</th>
                    <th>Customer</th>
                    <th>Item</th>
                    <th>Outstanding</th>
                    <th>Deliver Qty</th>
                    <th>Balance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>

            @foreach($rows as $row)
                <tr>
                    <td>{{ $row->so_number }}</td>
                    <td>{{ $row->customer_name }}</td>
                    <td>{{ $row->item_code }}</td>
                    <td>{{ $row->outstanding }}</td>
                    <td>{{ $row->delivery_qty }}</td>
                    <td>{{ $row->balance }}</td>
                    <td>{{ $row->status }}</td>
                </tr>
            @endforeach

            </tbody>
        </table>

    @endforeach
@endif



{{-- ================= WIP ================= --}}
@if($data->wip && $data->wip->count())
    <h2 style="margin-top:40px;">WIP</h2>

    @foreach($data->wip as $date => $rows)

        <h3>Delivery Date: {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</h3>

        <table border="1" cellpadding="5" cellspacing="0" width="100%" style="border-collapse: collapse;">
            <thead>
                <tr style="background:#f2f2f2;">
                    <th>SO</th>
                    <th>Customer</th>
                    <th>WIP Code</th>
                    <th>Outstanding</th>
                    <th>Req Qty</th>
                    <th>Balance WIP</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>

            @foreach($rows as $row)
                <tr>
                    <td>{{ $row->so_number }}</td>
                    <td>{{ $row->customer_name }}</td>
                    <td>{{ $row->wip_code }}</td>
                    <td>{{ $row->outstanding_wip }}</td>
                    <td>{{ $row->req_quantity }}</td>
                    <td>{{ $row->balance_wip }}</td>
                    <td>{{ $row->status }}</td>
                </tr>
            @endforeach

            </tbody>
        </table>

    @endforeach
@endif

@if(isset($data->payables) && $data->payables->count())
    <h2 style="margin-top:40px;">
        PRODUCTION PAYABLES
    </h2>

    <table border="1"
           cellpadding="5"
           cellspacing="0"
           width="100%"
           style="border-collapse: collapse;">

        <thead>
            <tr style="background:#f2f2f2;">
                <th>Document Number</th>
                <th>Posting Date</th>
                <th>Value Date</th>
                <th>Item No</th>
                <th>Item Description</th>
                <th>Quantity</th>
                <th>Remarks</th>
            </tr>
        </thead>

        <tbody>

        @foreach($data->payables as $payable)
            <tr>
                <td>{{ $payable->document_number }}</td>

                <td>
                    {{ \Carbon\Carbon::parse($payable->posting_date)->format('d M Y') }}
                </td>

                <td>
                    {{ \Carbon\Carbon::parse($payable->value_date)->format('d M Y') }}
                </td>

                <td>{{ $payable->item_no }}</td>

                <td>{{ $payable->item_description }}</td>

                <td>{{ number_format($payable->quantity) }}</td>

                <td>{{ $payable->remarks }}</td>
            </tr>
        @endforeach

        </tbody>

    </table>
@endif

</body>
</html>
