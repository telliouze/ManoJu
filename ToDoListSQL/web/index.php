<?php
    // La page d'accueil ne fait qu'un appel à app.php (qui contient toutes les routes vers les twigs
    $website = require_once __DIR__.'/../app/app.php';
    $website->run();
?>