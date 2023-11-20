<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  <h1>Welcome</h1>
  <table>
    <thead>
      <th>User ID</th>
      <th>User Name</th>
      <th>Wallet ID</th>
      <th>Type</th>
      <th>Deposit</th>
      <th>Widthraw</th>
      <th>Balance</th>
    </thead>
    <tbody>
      @foreach($ledger as $row)
      @php 
        $balance += $row->amount;
      @endphp
        <tr>
          <td>{{ $row->payable_id  ?? ''}}</td>
          <td>{{ $user->name ?? '' }}</td>
          <td>{{ $user->wallet->id ?? '' }}</td>
          <td>{{ $row->type ?? '' }}</td>
          <td>{{ $row->type === 'deposit' ? $row->amount : '-' }}</td>
          <td>{{ $row->type !== 'deposit' ? $row->amount : '-' }}</td>
          <td>{{ $balance ?? '-' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>  
</body>
</html>