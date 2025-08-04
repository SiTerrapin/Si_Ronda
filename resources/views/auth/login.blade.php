<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Form Login</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-container {
      background-color: #fff;
      padding: 40px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    button {
      width: 100%;
      padding: 10px;
      background-color: #4CAF50;
      border: none;
      color: white;
      border-radius: 4px;
      cursor: pointer;
    }
    button:hover {
      background-color: #45a049;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Login</h2>
    <form action="{{route('login.log')}}" method="post">
    @csrf
      <label for="username">Username</label>
      <input type="text" id="username" name="nama" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>

      <button type="submit">Masuk</button>
    </form>
  </div>
</body>
</html>