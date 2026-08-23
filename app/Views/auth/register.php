<div class="auth-container">
  <div class="text-center mb-8">
    <h2 style="font-size:32px;font-weight:800;letter-spacing:-0.025em;margin-bottom:8px">Create Account</h2>
    <p style="color:var(--muted)">Join the CoreQA community today</p>
  </div>

  <form method="post" action="/register" class="card">
    <?= $csrf->field() ?>
    <div style="margin-bottom:20px">
      <label>Full Name</label>
      <input type="text" name="name" required placeholder="Enter your full name">
    </div>
    <div style="margin-bottom:20px">
      <label>Email Address</label>
      <input type="email" name="email" required placeholder="your@email.com">
    </div>
    <div style="margin-bottom:24px">
      <label>Password</label>
      <input type="password" name="password" minlength="6" required placeholder="Minimum 6 characters">
    </div>
    <button class="btn" style="width:100%">Create my account</button>
    <p style="text-align:center;margin-top:24px;color:var(--muted);font-size:14px">
      Already have an account? <a href="/login" style="font-weight:600">Log in</a>
    </p>
  </form>
</div>
