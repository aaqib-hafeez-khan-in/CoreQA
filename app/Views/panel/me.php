<h2 style="margin:8px 0 16px">My Space</h2>

<?php if (!empty($_SESSION['flash'])): ?>
  <div class="card" style="margin-bottom:16px"><?= e($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<div class="grid grid-2">
  <div class="card">
    <h3>Account</h3>
    <form method="post" action="/me/account">
      <input type="hidden" name="_token" value="<?= e($csrf->token()) ?>">
      <div class="form-row">
        <label>Name</label>
        <input name="name" value="<?= e($profile['name'] ?? '') ?>" maxlength="120" required>
      </div>
      <div class="form-row">
        <label>Email</label>
        <input type="email" name="email" value="<?= e($profile['email'] ?? '') ?>" required>
      </div>
      <button class="btn" type="submit">Save account</button>
    </form>
  </div>

  <div class="card">
    <h3>Change password</h3>
    <form method="post" action="/me/password">
      <input type="hidden" name="_token" value="<?= e($csrf->token()) ?>">
      <div class="form-row">
        <label>Current password</label>
        <input type="password" name="current_password" autocomplete="current-password" required>
      </div>
      <div class="form-row">
        <label>New password</label>
        <input type="password" name="new_password" minlength="12" autocomplete="new-password" required>
      </div>
      <div class="form-row">
        <label>Confirm new password</label>
        <input type="password" name="new_password_confirmation" minlength="12" autocomplete="new-password" required>
      </div>
      <button class="btn" type="submit">Update password</button>
    </form>
  </div>
</div>

<div class="card" style="margin-top:16px">
  <div class="form-row">
    <a class="btn" href="/topics/new">Create a topic</a>
    <a class="btn ghost" href="/topics">Browse topics</a>
  </div>
</div>

<div class="grid grid-2" style="margin-top:16px">
  <div class="card">
    <h3>My recent topics</h3>
    <ul class="list">
      <?php foreach($my_topics as $t): ?>
        <li class="item">
          <div>
            <a href="/topics/<?= $t['id'].'-'.$t['slug'] ?>"><strong><?= e($t['title']) ?></strong></a>
            <div class="meta"><span class="badge <?= e($t['status']) ?>"><?= e($t['status']) ?></span> • <?= e($t['created_at']) ?></div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="card">
    <h3>My latest replies</h3>
    <ul class="list">
      <?php foreach($my_posts as $p): ?>
        <li class="item">
          <div>
            <a href="/topics/<?= $p['topic_id'].'-'.$p['slug'] ?>"><strong><?= e($p['title']) ?></strong></a>
            <div class="meta">Replied on <?= e($p['created_at']) ?></div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
