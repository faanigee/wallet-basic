<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Report</title>
</head>

<body>
  <h1>Welcome</h1>
  <table>
    <thead>
      <th>User ID</th>
      <th>Wallet ID</th>
      <th>Amount</th>
    </thead>
    <tbody>
      @if($wallets->count() > 0)
      @foreach($wallets as $row)
      <tr>
        <td>{{ $row->holder_id  ?? ''}}</td>
        <td>{{ $row->id ?? '' }}</td>
        <td>{{ $row->balance ?? '' }}</td>
      </tr>
      @endforeach
      @else
      <tr>
        <td colspan=3>Conratulations...!!! (No Defaulter Found)</td>
      </tr>
      @endif
    </tbody>
  </table>
</body>

</html>