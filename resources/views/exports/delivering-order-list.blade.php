<table>
    <tr><th style="font-weight: bold" colspan="5"><h3>Delivering Status</h3></th></tr>
</table>

<table style="border: 1px solid black; border-collapse: collapse; width: 100%">
    <thead>
    <tr>
        <th style="font-weight: bold; border: 1px solid black; border-collapse: collapse; width: 100px">No.</th>
        <th style="font-weight: bold; border: 1px solid black; border-collapse: collapse; width: 100px">Contract No.</th>
        <th style="font-weight: bold; border: 1px solid black; border-collapse: collapse; width: 100px">ETA</th>
        <th style="font-weight: bold; border: 1px solid black; border-collapse: collapse; width: 100px">Part No.</th>
        <th style="font-weight: bold; border: 1px solid black; border-collapse: collapse; width: 100px">Part Color Code</th>
        <th style="font-weight: bold; border: 1px solid black; border-collapse: collapse; width: 100px">Part Group</th>
        <th style="font-weight: bold; border: 1px solid black; border-collapse: collapse; width: 100px">Quantity</th>
        <th style="font-weight: bold; border: 1px solid black; border-collapse: collapse; width: 100px">Procurement Supplier Code</th>
        <th style="font-weight: bold; border: 1px solid black; border-collapse: collapse; width: 100px">Plant Code</th>
        <th style="font-weight: bold; border: 1px solid black; border-collapse: collapse; width: 100px">Input File Name</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orderLists as $index => $order)
        <tr>
            <td style="border: 1px solid black; border-collapse: collapse; text-align: left">{{ ($index + 1) . ' ' }}</td>
            <td style="border: 1px solid black; border-collapse: collapse;text-align: left;">{{ $order['contract_code'] }}</td>
            <td style="border: 1px solid black; border-collapse: collapse;text-align: left;">{{  $order['eta'] ? Carbon\Carbon::createFromFormat('Y-m-d',$order['eta'])->format('d/m/Y') : '' }}</td>
            <td style="border: 1px solid black; border-collapse: collapse;text-align: left;">{{ $order['part_code'] }}</td>
            <td style="border: 1px solid black; border-collapse: collapse; text-align: left">{{ '' . $order['part_color_code'] }}</td>
            <td style="border: 1px solid black; border-collapse: collapse;text-align: left;">{{ $order['part_group'] }}</td>
            <td style="border: 1px solid black; border-collapse: collapse; text-align: left">{{ number_format($order['actual_quantity']) ?: '' }}</td>
            <td style="border: 1px solid black; border-collapse: collapse; text-align: left">{{ $order['supplier_code'] }}</td>
            <td style="border: 1px solid black; border-collapse: collapse; text-align: left">{{ $order['plant_code'] }}</td>
            <td style="border: 1px solid black; border-collapse: collapse;text-align: left;">{{ $order->fileImport ? $order->fileImport->original_file_name : 'manual order' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
