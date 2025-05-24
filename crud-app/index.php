<?php
session_start();
require 'config.php';

$search = '';
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

// Pagination setup
$limit = 5; // posts per page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Fetch posts
$query = "SELECT * FROM posts";
if ($search !== '') {
    $query .= " WHERE title LIKE '%$search%' OR content LIKE '%$search%'";
}
$query .= " ORDER BY created_at DESC LIMIT $start, $limit";
$result = mysqli_query($conn, $query);

// Get total for pagination
$countQuery = "SELECT COUNT(*) as total FROM posts";
if ($search !== '') {
    $countQuery .= " WHERE title LIKE '%$search%' OR content LIKE '%$search%'";
}
$countResult = mysqli_query($conn, $countQuery);
$totalPosts = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalPosts / $limit);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Blog Posts</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">MyBlog</a>
    <div class="d-flex">
      <?php if (isset($_SESSION['username'])): ?>
        <span class="navbar-text text-white me-3">
          Logged in as <?= htmlspecialchars($_SESSION['username']) ?>
        </span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-outline-light btn-sm me-2">Login</a>
        <a href="register.php" class="btn btn-outline-light btn-sm">Register</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="container mt-5">
    <div class="d-flex justify-content-between mb-3">
        <form method="GET" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Search posts..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        <a href="create.php" class="btn btn-success">Create New Post</a>
    </div>

    <h3>Posts</h3>
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5><?= htmlspecialchars($row['title']) ?></h5>
                    <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                    <small class="text-muted">Posted on <?= $row['created_at'] ?></small><br>
                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this post?');">Delete</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-warning">No posts found.</div>
    <?php endif; ?>

    <!-- Pagination -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>
</body>
</html>
