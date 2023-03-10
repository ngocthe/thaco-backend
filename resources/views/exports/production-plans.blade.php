<table>
    <tr><th style="font-weight: bold" colspan="5"><h3>Production Plan</h3></th></tr>
</table>

<br>
<table style="border: 1px solid black; border-collapse: collapse;">
    <thead>
    <tr>
        @if($type == 'pdf')
            <th rowspan="2" style="width: 30px;font-weight: bold; border: 1px solid black; border-collapse: collapse;">No</th>
        @endif
        <th rowspan="2" style="width: 80px; font-weight: bold; border: 1px solid black; border-collapse: collapse;">MSC Code</th>
        <th rowspan="2" style="width: 150px; font-weight: bold; border: 1px solid black; border-collapse: collapse;">Exterior Color Code</th>
        <th rowspan="2" style="width: 200px; font-weight: bold; border: 1px solid black; border-collapse: collapse;">Description</th>
        <th rowspan="2" style="width: 200px; font-weight: bold; border: 1px solid black; border-collapse: collapse;">Plant Code</th>
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
    @foreach($productionPlans as $index => $plant)
        <tr>
            @if($type == 'pdf')
                <td style="border: 1px solid black; border-collapse: collapse;">{{ ($index + 1) . ' ' }}</td>
            @endif
            <td style="border: 1px solid black; border-collapse: collapse;">{{ $plant['msc_code'] }}</td>
            <td style="border: 1px solid black; border-collapse: collapse;">{{ $plant['vehicle_color_code'] }}</td>
            <td style="border: 1px solid black; border-collapse: collapse;">{{ $plant['msc_description'] }}</td>
            <td style="border: 1px solid black; border-collapse: collapse;">{{ $plant['plant_code'] }}</td>
            @foreach($plant['production_plans'] as $volume)
                <td style="text-align: left; border: 1px solid black; border-collapse: collapse;">{{ number_format($volume) ?: '' }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
