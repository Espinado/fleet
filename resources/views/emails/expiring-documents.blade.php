<h2>Documents expiring soon</h2>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Type</th>
            <th>Name</th>
            <th>Document</th>
            <th>Expiry Date</th>
            <th>Days Left / Overdue</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $item->type }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->document }}</td>
                <td>{{ $item->expiry_date->format('Y-m-d') }}</td>
                <td>
                    @if($item->days_left >= 0)
                        {{ $item->days_left }} days left
                    @else
                        Overdue by {{ abs($item->days_left) }} days
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
