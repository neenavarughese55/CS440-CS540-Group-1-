<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Login Successful | BookSmart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{ --primary:#0052cc; --grey:#f5f5f5; --text:#333; }
    *{ margin:0; padding:0; box-sizing:border-box; }
    body{ font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,"Noto Sans",sans-serif; background:var(--grey); color:var(--text); display:flex; flex-direction:column; min-height:100vh; }
    .header{ background:#fff; border-bottom:1px solid #ddd; padding:1rem 0; }
    .container{ max-width:960px; margin:0 auto; padding:0 1rem; }
    .nav{ display:flex; justify-content:space-between; align-items:center; }
    .nav ul{ list-style:none; display:flex; gap:1rem; }
    .main{ flex:1; display:flex; align-items:center; justify-content:center; padding:3rem 0; }
    .card{ background:#fff; padding:2.5rem 2rem; border-radius:6px; width:100%; max-width:420px; text-align:center; }
    .card h2{ margin-bottom:1rem; color:var(--primary); }
    .card p{ margin-bottom:1.5rem; line-height:1.5; }
    .card a.btn{ display:inline-block; background:var(--primary); color:#fff; padding:.6rem 1.2rem; border-radius:4px; text-decoration:none; }
    .footer{ text-align:center; padding:1.5rem 0; font-size:.875rem; color:#666; background:#fff; border-top:1px solid #ddd; }
  </style>
</head>

<body>
<!-- ===== Header ===== -->
<header class="header">
  <div class="container">
    <nav class="nav">
      <div class="logo"><a href="./">BookSmart</a></div>
      <ul>
        <li><a href="./">Home</a></li>
        <li><a href="login-register.html">Login</a></li>
      </ul>
    </nav>
  </div>
</header>

<!-- ===== Main Content ===== -->
<main class="main">
  <div class="card">
    <h2>Login Successful!</h2>
    <p>You are now logged in. You can start booking or manage your account.</p>

    <a href="${pageContext.request.contextPath}/" class="btn">Go to Home</a>
  </div>
</main>

<!-- ===== Footer ===== -->
<footer class="footer">
  <div class="container">
    <p>&copy; 2025 BookSmart · All rights reserved.</p>
  </div>
</footer>
</body>
</html>