<div class="auth-container">
  <div class="text-center mb-8">
    <h2 style="font-size:32px;font-weight:800;letter-spacing:-0.025em;margin-bottom:8px">Welcome Back</h2>
    <p style="color:var(--muted)">Login to your CoreQA account</p>
  </div>

  <form method="post" action="/login" class="card">
    <?= $csrf->field() ?>
    <div style="margin-bottom:20px">
      <label>Email Address</label>
      <input type="email" name="email" required placeholder="your@email.com">
    </div>
    <div style="margin-bottom:24px">
      <label>Password</label>
      <input type="password" name="password" required placeholder="Enter your password">
    </div>
    <button class="btn" style="width:100%">Log in</button>
    <p style="text-align:center;margin-top:24px;color:var(--muted);font-size:14px">
      Don't have an account? <a href="/register" style="font-weight:600">Sign up</a>
    </p>
  </form>
</div>
