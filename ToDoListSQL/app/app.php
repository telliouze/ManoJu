<?php
    // Toutes les routes et les actions associées

    // Autoload généré par composer: permet de loader toutes les bibliothèques contenures dans /vendor
    require_once __DIR__."/../vendor/autoload.php";

    // Appel des classes
    require_once __DIR__."/../src/Task.php";
    require_once __DIR__."/../src/Category.php";

    // Création de l'application Silex
    $app = new Silex\Application();

    // Définition des données de connexion à la base de données
    $server = 'mysql:host=localhost:3306;dbname=to_do';
    $username = 'root';
    $password = 'root';
    // Création d'une variable globale pour la BDD, instance de la classe PDO (PHP Data Object)
    $DB = new PDO($server,$username,$password);

    // $config = new \Doctrine\DBAL\Configuration();
    // $connectionParams = array(
    // 'dbname' => 'to_do',
    // 'user' => 'root',
    // 'password' => 'root',
    // 'host' => 'localhost:3306',
    // 'pdo' => $DB,
    // );
    // $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

    $app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'dbhost' => 'localhost:3306',
        'dbname' => 'to_do',
        'user' => 'root',
        'password' => 'root',
        'pdo' => $DB
    ),
    ));

    //Ajout de la syntaxe TWIG à notre application
    $app->register(new Silex\Provider\TwigServiceProvider(),array('twig.path' => __DIR__.'/../views'));
    
    // Gestion des users et de leur session
    $app->register(new Silex\Provider\SessionServiceProvider());

    $app->register(new Silex\Provider\UrlGeneratorServiceProvider());

 $app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'foo' => array('pattern' => '^/foo'), // Exemple d'une url accessible en mode non connecté
        'default' => array(
            'pattern' => '^.*$',
            'anonymous' => true, // Indispensable car la zone de login se trouve dans la zone sécurisée (tout le front-office)
            'form' => array('login_path' => '/', 'check_path' => '  connexion'),
            'logout' => array('logout_path' => '/deconnexion'), // url à appeler pour se déconnecter
            'users' => $app->share(function() use ($app) {
                // La classe App\User\UserProvider est spécifique à notre application et est décrite plus bas
                return new App\User\UserProvider($app['db']);
            }),
        ),
    ),
    'security.access_rules' => array(
        // ROLE_USER est défini arbitrairement, vous pouvez le remplacer par le nom que vous voulez
        array('^/.+$', 'ROLE_USER'),
        array('^/foo$', ''), // Cette url est accessible en mode non connecté
    )
));

    // Request va nous permettre d'utiliser des méthodes non gérées par les browser (PATCH et DELETE par exemple)
    use Symfony\Component\HttpFoundation\Request;
    Request::enableHttpMethodParameterOverride();

    // Route de base
    $app->get('/', function(Request $request) use ($app) {
    /*return $app['twig']->render('index.html.twig', array(
        'error' => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));*/
    return $app['twig']->render('index.html.twig');

    });
    
    // On utilise $app pour pouvoir utiliser TWIG
    $app->get("/foo", function() use ($app) {
        // Ici, nous allons générer index.html.twig avec la liste des catégories comme paramètre (méthode statique getAll())
        return $app['twig']->render('foo.html.twig', array('categories' => Category::getAll($app['db'])));
    });

    // Les méthodes GET et POST de la route /tasks vont déclencher 2 actions différentes
    // GET : affichage de toutes les tâches (avec le nom de la catégorie)
    $app->get("/tasks", function() use ($app) {
        return $app['twig']->render('tasks.html.twig', array('tasks'=>Task::getAll()));
    });

    // POST : la méthode est issue du formulaire de category.html.twig
    $app->post("/tasks", function() use ($app) {
        $description = $_POST['description'];
        $category_id = $_POST['category_id'];
        $task = new Task($description, $category_id);
        $task->save();
        $category = Category::find($category_id);
        return $app['twig']->render('category.html.twig', array('category' => $category, 'tasks' => $category->getTasks()));
    });

    // Les méthodes GET et POST de la route /categories vont déclencher 2 actions différentes
    // GET : Affichage de la liste des catégories
    $app->get("/categories", function() use ($app) {
        return $app['twig']->render('categories.html.twig', array('categories'=>Category::getAll()));
    });

    // POST : issue du formulaire de index.html.twig : création d'une nouvelle catégorie
    $app->post("/categories", function() use ($app){
        $category = new Category($_POST['name']);
        $category->save();
        // On regénére la page index.html.twig qui va afficher la liste de toutes les catégories (y compris la nouvelle)
        return $app['twig']->render('index.html.twig', array('categories'=>Category::getAll()));
    });

    // GET : Afficher les tâches d'une catégorie
    $app->get("/categories/{id}", function($id) use ($app){
        $category = Category::find($id);
        return $app['twig']->render('category.html.twig', array('category' => $category, 'tasks' => $category->getTasks()));
    });

    // DELETE (override) : suppression d'une catégorie et de son contenu
    $app->delete("/categories/{id}", function($id) use ($app){
        $category = Category::find($id);
        $category->delete();
        return $app['twig']->render('index.html.twig', array('categories' => Category::getAll()));
    });

    // Envoi vers le formulaire de modification d'une catégorie
    $app->get("/categories/{id}/edit", function($id) use ($app){
        $category = Category::find($id);
        return $app['twig']->render('category_edit.html.twig', array('category' => $category));
    });

    // PATCH (override) : validation du formulaire de modification d'un nom de catégorie
    $app->patch("/categories/{id}", function($id) use ($app){
        $name = $_POST['name'];
        $category = Category::find($id);
        $category->update($name);
        return $app['twig']->render('category.html.twig', array('category' => $category, 'tasks' => $category->getTasks()));
    });

    // Suppression d'une catégorie et de toutes les tâches associées
    $app->post("/delete_categories", function() use ($app){
        Category::deleteAll();
        return $app['twig']->render('index.html.twig');
    });

    // Suppression des tâches d'une catégorie
    $app->post("/delete_tasks", function() use ($app){
        Task::deleteAll();
        return $app['twig']->render('index.html.twig');
    });

    return $app;    
?>