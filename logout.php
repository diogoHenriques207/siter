<?php
session_start();
session_destroy();

// redirect limpo
header("Location: index.php?logout=1");
exit;