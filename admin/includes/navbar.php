<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="http://localhost/xampp/MARS/myPrj/admin">DASHBOARD</a>
    <?php if (isset($_SESSION['user'])): ?>
      <h3 class="text-white">Welcome,
        <?= $_SESSION['user']['name'] ?>
      </h3>
    <?php endif; ?>
    <div class="d-flex gap-2">
      <a class="navbar-brand" href="http://localhost/xampp/MARS/myPrj/admin/"><i class="fa-solid fa-house"></i></a>
      <?php if (basename($_SERVER['PHP_SELF']) == 'index.php'): ?>
        <!-- <a href="http://localhost/xampp/MARS/myPrj/admin/register.php" class="btn btn-primary">Register</a> -->
      <?php elseif (basename($_SERVER['PHP_SELF']) == 'register.php'): ?>
        <a href="http://localhost/xampp/MARS/myPrj/admin/index.php" class="btn btn-primary">Login</a>
      <?php endif; ?>
      <?php if (isset($_SESSION['user'])): ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
          <button type="submit" class="btn btn-primary" name="logout">Logout</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</nav>