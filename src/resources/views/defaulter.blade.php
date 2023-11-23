<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <title>Report</title>
</head>

<body>
  <h2>Defaulter Users (Negative Balance)</h2>

  <div class="table-responsive">
    <table class="table">
      <thead class="table-dark">
        <th>User ID</th>
        <th>Wallet ID</th>
        <th>Amount</th>
        <th>Status</th>
      </thead>
      <tbody>
        @if($wallets->count() > 0)
        @foreach($wallets as $row)
        <tr>
          <td>{{ $row->holder_id  ?? ''}}</td>
          <td>{{ $row->id ?? '' }}</td>
          <td>{{ $row->balance ?? '' }}</td>
          <td>{{ $row->status ?? '' }}</td>
        </tr>
        @endforeach
        @else
        <tr>
          <td colspan=4 align="center">Conratulations...!!! (No Defaulter Found)</td>
        </tr>
        @endif
      </tbody>
    </table>
  </div>
  <br />
  <h2>Defaulter Users (Wrong Transaction Balance)</h2>
  <div class="table-responsive">
    <table class="table">
      <thead class="table-dark">
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
          <td colspan=5 align="center">Conratulations...!!! (No Defaulter Found)</td>
        </tr>
        @endif
      </tbody>
    </table>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

</html>