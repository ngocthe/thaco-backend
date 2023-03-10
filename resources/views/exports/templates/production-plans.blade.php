<table>
    <thead>
    <tr><th style="font-weight: bold" colspan="4"><h3>MRP Import Production Schedule</h3></th></tr>
    <tr>
        <th style="font-weight: bold" colspan="2">MRP Run Month:</th>
        <th style="font-weight: bold">N  :</th>
        <th style="font-weight: bold">{{ $months[0]['format'] }}</th>
    </tr>
    <tr>
        <th style="font-weight: bold" colspan="2">MRP Target Month:</th>
        <th style="font-weight: bold">N+3 :</th>
        <th style="font-weight: bold">{{ $months[3]['format'] }}</th>
    </tr>
    <tr>
        <th></th>
        <th style="font-weight: bold" colspan="3">*) To Update/Import by monthly</th>
    </tr>
    <tr>
        <th></th>
        <th style="font-weight: bold" colspan="3">*) To Extend one more month by monthly</th>
    </tr>
    <tr>
        <th style="width: 30px; border: 1px solid black; border-collapse: collapse; background-color: #fce4d6"></th>
        <th style="width: 80px; font-weight: bold;">: Must Input Volume</th>
        <th style="width: 100px;"></th>
        <th style="width: 150px;"></th>
        <th style="width: 80px; border-right: 1px solid black;"></th>
        @foreach($months as $month)
            @php($totalCells = count($month['weeks']) * 7)
            @for($i = 1; $i <= $totalCells; $i ++)
                <th style="font-weight: bold; border: 1px solid black; @if($i < $totalCells)border-right: none; @endif border-left: none; border-collapse: collapse;">
                    @if($i == 3)
                        {{ $month['name'] }}
                    @endif
                </th>
            @endfor
        @endforeach
    </tr>
    <tr>
        <th style="border: 1px solid black; border-collapse: collapse; background-color: #ffff00"></th>
        <th style="font-weight: bold;">: Sunday</th>
        <th></th>
        <th></th>
        <th style="border-right: 1px solid black;"></th>
        @foreach($months as $month)
            @php($totalCells = count($month['weeks']) * 7)
            @for($i = 1; $i <= $totalCells; $i ++)
                <th style="font-weight: bold; border: 1px solid black; @if($i < $totalCells) border-right: none; @endif border-left: none; border-collapse: collapse;">
                    @if($i == 3)
                        {{ $month['format']  }}
                    @endif
                </th>
            @endfor
        @endforeach
    </tr>
    <tr>
        <th style="border: 1px solid black; border-collapse: collapse; background-color: #d9e1f2"></th>
        <th style="font-weight: bold;">: No Production Day</th>
        <th></th>
        <th></th>
        <th></th>
        @foreach($weeks as $week)
            <th colspan="7" style="text-align: center; font-weight: bold; border: 1px solid black; border-collapse: collapse;">{{ $week['title'] }}</th>
        @endforeach
    </tr>
    <tr>
        <th style="font-weight: bold; border: 1px solid black; border-collapse: collapse;">No.</th>
        <th style="font-weight: bold; border: 1px solid black; border-collapse: collapse;">MSC</th>
        <th style="font-weight: bold; border: 1px solid black; border-collapse: collapse;">Ext.Color</th>
        <th style="font-weight: bold; border: 1px solid black; border-collapse: collapse;">Description</th>
        <th style="font-weight: bold; border: 1px solid black; border-collapse: collapse;">Plant Code</th>
        @foreach($dates as $date)
            <th style="width: 30px; text-align: center; border: 1px solid black; border-collapse: collapse;">{{ $date['format'] }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @for($i = 1; $i <= 10; $i ++)
        <tr>
            <th style="border: 1px solid black; border-collapse: collapse;">{{ $i }}</th>
            <th style="border: 1px solid black; border-collapse: collapse;"></th>
            <th style="border: 1px solid black; border-collapse: collapse;"></th>
            <th style="border: 1px solid black; border-collapse: collapse;"></th>
            <th style="border: 1px solid black; border-collapse: collapse;"></th>
            @foreach($dates as $index => $date)
                <th style="
                width: 30px;
                text-align: center;
                border: 1px solid black;
                border-collapse: collapse;
                @if($index % 7 == 0)
                background-color: #fce4d6
                @elseif($index % 7 == 6)
                background-color: #ffff00
                @elseif($date['day_off'])
                background-color: #d9e1f2
                @endif"></th>
            @endforeach
        </tr>
    @endfor
    </tbody>
</table>
