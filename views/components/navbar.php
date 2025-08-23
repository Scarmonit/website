<header role="banner">
    <nav role="navigation" aria-label="Main navigation">
        <ul>
            <li><a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'class="active" aria-current="page"' : ''; ?>>Home</a></li>
            <li><a href="file-viewer.php" <?php echo basename($_SERVER['PHP_SELF']) === 'file-viewer.php' ? 'class="active" aria-current="page"' : ''; ?>>File Viewer</a></li>
            <li><a href="me.php" <?php echo basename($_SERVER['PHP_SELF']) === 'me.php' ? 'class="active" aria-current="page"' : ''; ?>>About Me</a></li>
        </ul>
    </nav>
    <a href="logout.php" class="logout-btn" aria-label="Logout from Parker Directory">Logout</a>
</header>