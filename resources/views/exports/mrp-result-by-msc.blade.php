<table>
    <tr><th style="font-weight: bold" colspan="5"><h3>MRP Result</h3></th></tr>
</table>
<table style="border: 1px solid black; border-collapse: collapse;">
    <thead>
    <tr>
        <th rowspan="2" colspan="4" style="font-weight: bold; border: 1px solid black; border-collapse: collapse;">Planned Production Date</th>
        @foreach($weeks as $week)
            <th colspan="{{ count($week['dates']) }}" style="text-align: center; font-weight: bold; border: 1px solid black; border-collapse: collapse;">{{ $week['title'] }}</th>
        @endforeach
    </tr>
    <tr>
        @foreach($dates as $date)
            <th style="width: 80px; text-align: center; font-weight: bold; border: 1px solid black; border-collapse: collapse;">{{ $date }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($mscData as $msc)
        <tr>
            <td style="width:80px; border: 1px solid black; border-collapse: collapse; font-weight: bold" rowspan="2">MSC</td>
            <td style="width:150px; border: 1px solid black; border-collapse: collapse; text-align: left; font-weight: bold" rowspan="2">Exterior Color Code</td>
            <td style="border: 1px solid black; border-collapse: collapse; font-weight: bold"  colspan="2">Production Volume</td>
            @foreach($msc['days'] as $day => $volume)
                <td style="text-align: left; border: 1px solid black; border-collapse: collapse;" rowspan="2">{{ number_format($volume) ?: '' }} </td>
            @endforeach
        </tr>
        <tr>
            <td style="width:120px; border: 1px solid black; border-collapse: collapse; font-weight: bold" >Part No</td>
            <td style="width:120px; border: 1px solid black; border-collapse: collapse; font-weight: bold" >Part Color Code</td>
        </tr>
        @foreach($msc['data'] as $data)
            <tr>
                <td style="border: 1px solid black; border-collapse: collapse; text-align: left">{{ $data['msc_code'] }}</td>
                <td style="border: 1px solid black; border-collapse: collapse; text-align: left">{{ $data['vehicle_color_code'] }}</td>
                <td style="border: 1px solid black; border-collapse: collapse; text-align: left">{{ $data['part_code'] }}</td>
                <td style="border: 1px solid black; border-collapse: collapse; text-align: left">{{ $data['part_color_code'] }}</td>
                @foreach($data['production_dates'] as $qty)
                    <td style="text-align: left; border: 1px solid black; border-collapse: collapse;">{{ number_format($qty) ?: '' }} </td>
                @endforeach
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>
