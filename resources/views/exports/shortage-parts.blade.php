<table>
    <tr><th style="font-weight: bold" colspan="5"><h3>Shortage Part</h3></th></tr>
</table>
<table style="border: 1px solid black; border-collapse: collapse;">
    <thead>
    <tr>
        <th rowspan="3" style="width: 80px; font-weight: bold; border: 1px solid black; border-collapse: collapse;">Part No.</th>
        <th rowspan="3" style="width: 110px; font-weight: bold; border: 1px solid black; border-collapse: collapse;">Part Color Code</th>
        <th colspan="{{ count($dates) }}" style="font-weight: bold; border: 1px solid black; border-collapse: collapse; text-align: center">
            Planned Production Date
        </th>
    </tr>
    <tr>
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
    @foreach($planDates as $plant)
        <tr>
            <td style="border: 1px solid black; border-collapse: collapse;">{{ $plant['part_code'] }}</td>
            <td style="border: 1px solid black; border-collapse: collapse; text-align: left">{{ $plant['part_color_code'] }}</td>
            @foreach($plant['plan_dates'] as $qty)
                <td style="text-align: left; border: 1px solid black; border-collapse: collapse;">
                    @if ($qty < 0)
                    ({{ number_format(abs($qty)) }})
                    @endif
                </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
