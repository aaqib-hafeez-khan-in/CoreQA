<section class="hero">
  <div class="container">
    <h1>Knowledge Sharing, Evolved.</h1>
    <p>Join CoreQA to collaborate, solve problems, and share expertise with a professional community of developers and experts.</p>
    <?php if (!$auth->check()): ?>
      <div style="display:flex;justify-content:center;gap:16px;margin-top:40px">
        <a class="btn" href="/register">Join the community</a>
        <a class="btn ghost" href="/login">Sign in</a>
      </div>
    <?php else: ?>
      <div style="margin-top:40px">
        <a class="btn" href="/topics/new">Ask a Question</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<div class="grid-2">
  <div>
    <form class="card" method="get" action="/topics" style="display:flex;gap:12px;margin-bottom:24px;padding:16px">
      <input type="text" name="search" placeholder="Search topics, questions, and more..." value="<?= e($q ?? '') ?>" style="flex:1">
      <button class="btn">Search</button>
    </form>

    <div class="card" style="padding:0">
      <div style="padding:20px;border-bottom:1px solid var(--border)">
        <h3 style="margin:0;font-size:18px;font-weight:700">Recent Discussions</h3>
      </div>
      <ul class="list">
        <?php foreach ($topics as $t): ?>
          <li class="item" style="padding:20px">
            <div>
              <a href="/topics/<?= $t['id'] . '-' . e($t['slug']) ?>" style="font-size:18px;font-weight:600;display:block;margin-bottom:8px"><?= e($t['title']) ?></a>
              <div class="meta" style="font-size:13px;color:var(--muted)">
                <?php if (!empty($t['pinned_at'])): ?><span class="badge pin" style="color:var(--primary);background:var(--primary-100);padding:2px 8px;border-radius:4px;font-size:11px;font-weight:bold;margin-right:8px">PINNED</span><?php endif; ?>
                <span style="display:inline-flex;align-items:center;gap:4px">
                  <span style="width:8px;height:8px;border-radius:50%;background:<?= $t['status'] === 'open' ? 'var(--success)' : 'var(--muted)' ?>"></span>
                  <?= ucfirst($t['status']) ?>
                </span>
                • Created on <?= date('M j, Y', strtotime($t['created_at'])) ?>
              </div>
            </div>
            <div style="text-align:right;color:var(--muted);font-size:13px">
              <strong>12</strong> replies
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <aside>
    <div class="card" style="margin-bottom:24px">
      <h3 style="margin:0 0 16px;font-size:16px;font-weight:700">Trending Tags</h3>
      <div style="display:flex;flex-wrap:wrap;gap:8px">
        <?php foreach ($tags as $tg): ?>
          <a href="/topics?tag=<?= e($tg['slug']) ?>" class="btn ghost sm" style="border-radius:999px;font-size:12px"><?= e($tg['name']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card">
      <h3 style="margin:0 0 16px;font-size:16px;font-weight:700">Quick Stats</h3>
      <div style="font-size:14px;color:var(--muted)">
        <div style="display:flex;justify-content:space-between;margin-bottom:8px">
          <span>Active Topics</span>
          <strong style="color:var(--fg-color)">432</strong>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:8px">
          <span>Total Members</span>
          <strong style="color:var(--fg-color)">1.2k</strong>
        </div>
        <div style="display:flex;justify-content:space-between">
          <span>Daily Votes</span>
          <strong style="color:var(--fg-color)">89</strong>
        </div>
      </div>
    </div>
  </aside>
</div>

<div style="margin-top:32px">
  <?php require __DIR__ . '/partials/pagination.php'; ?>
</div>
