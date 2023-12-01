<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" type="text/css" href="{{asset('vendor/bootstrap/css/bootstrap.min.css')}}" />
  <title>Report</title>
</head>

<body>

  <h1>Defaulter Users (Wrong Transaction Balance)</h1>
  <table class="table">
    <thead class="thead-dark">
      <th>User ID</th>
      <th>Wallet ID</th>
      <th>Wallet Balance</th>
      <th>Transactions Balance</th>
      <th>Status</th>
    </thead>
    <tbody>
      @if(count($data) > 0)
      @foreach($data as $row)
      <tr>
        <td>{{ $row['holder_id']  ?? ''}}</td>
        <td>{{ $row['id'] ?? '' }}</td>
        <td>{{ $row['wallet_balance'] ?? '' }}</td>
        <td>{{ $row['transactions_balance'] ?? '' }}</td>
        <td>{{ $row['status'] ?? '' }}</td>
      </tr>
      @endforeach
      @else
      <tr>
        <td colspan=3>Conratulations...!!! (No Defaulter Found)</td>
      </tr>
      @endif
    </tbody>
  </table>
  <script src="{{asset('vendor/jquery/jquery.min.js')}}"></script>
  <script src="{{asset('vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
</body>

</html>