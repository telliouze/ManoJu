<?php

    use Silex\Provider\MonologServiceProvider;
    use Silex\Provider\WebProfilerServiceProvider;
    use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
    use Silex\Provider\UrlGeneratorServiceProvider;
    use App\Provider\UserProvider;  
    
    // Toutes les routes et les actions associées

    // Autoload généré par composer: permet de loader toutes les bibliothèques contenures dans /vendor
    require_once __DIR__."/../vendor/autoload.php";

    // Appel des classes
    require_once __DIR__."/../src/Task.php";
    require_once __DIR__."/../src/Category.php";
    require_once __DIR__."/../src/User.php";

    // Création de l'application Silex
    $app = new Silex\Application();

    // Définition des données de connexion à la base de données
    $server = 'mysql:host=localhost:3306;dbname=to_do';
    $username = 'root';
    $password = 'root';
    // Création d'une variable globale pour la BDD, instance de la classe PDO (PHP Data Object)
    $DB = new PDO($server,$username,$password);

    //Ajout de la syntaxe TWIG à notre application
    $app->register(new Silex\Provider\TwigServiceProvider(),array('twig.path' => __DIR__.'/../views'));
    
    // Gestion des users et de leur session
    $app->register(new Silex\Provider\SessionServiceProvider());

    $app->register(new Silex\Provider\UrlGeneratorServiceProvider());

    // Request va nous permettre d'utiliser des méthodes non gérées par les browser (PATCH et DELETE par exemple)
    use Symfony\Component\HttpFoundation\Request;
    Request::enableHttpMethodParameterOverride();

    /*******************************************
    DEBUT CODE CLEMENT
    ********************************************/
    $app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'default' => array(
            'pattern' => '^/todolist.*$',
            'form' => array('login_path' => '/login', 'check_path' => '/todolist/login'),
            'logout' => array('logout_path' => '/todolist/logout', 'invalidate_session' => true),
            'users' => function () use ($app) {
                return new UserProvider();
            },
        )
    ),
    'security.default_encoder' => function () use ($app) {
        return new PlaintextPasswordEncoder();
    },
    'security.access_rules' => array(
        array('^/todolist/.*$', 'ROLE_USER')
    )
    ));

    $app->register(new MonologServiceProvider(), array(
        'monolog.logfile' => __DIR__.'/../var/logs/silex_dev.log',
    ));

    $app->register(new WebProfilerServiceProvider(), array(
        'profiler.cache_dir' => __DIR__.'/../var/cache/profiler',
    ));
    /*******************************************
    FIN CODE CLEMENT
    ********************************************/
    
    
    // Route de base
    $app->get('/todolist', function(Request $request) use ($app) {
    return $app['twig']->render('index.html.twig', array('categories' => Category::getAll()));
    });

    // Les méthodes GET et POST de la route /tasks vont déclencher 2 actions différentes
    // GET : affichage de toutes les tâches (avec le nom de la catégorie)
    $app->get("/tasks", function() use ($app) {
        return $app['twig']->render('tasks.html.twig', array('tasks'=>Task::getAll()));
    });

    // POST : la méthode est issue du formulaire de category.html.twig
    $app->post("/todolist/tasks", function() use ($app) {
        $description = $_POST['description'];
        $category_id = $_POST['category_id'];
        $task = new Task($description, $category_id);
        $task->save();
        $category = Category::find($category_id);
        return $app['twig']->render('category.html.twig', array('category' => $category, 'tasks' => $category->getTasks()));
    });

    // Les méthodes GET et POST de la route /categories vont déclencher 2 actions différentes
    // GET : Affichage de la liste des catégories
    $app->get("/todolist/categories", function() use ($app) {
        return $app['twig']->render('categories.html.twig', array('categories'=>Category::getAll()));
    });

    // POST : issue du formulaire de index.html.twig : création d'une nouvelle catégorie
    $app->post("/todolist/categories", function() use ($app){
        $category = new Category($_POST['name']);
        $category->save();
        // On regénére la page index.html.twig qui va afficher la liste de toutes les catégories (y compris la nouvelle)
        return $app['twig']->render('index.html.twig', array('categories'=>Category::getAll()));
    });

    // GET : Afficher les tâches d'une catégorie
    $app->get("/todolist/categories/{id}", function($id) use ($app){
        $category = Category::find($id);
        return $app['twig']->render('category.html.twig', array('category' => $category, 'tasks' => $category->getTasks()));
    });

    // DELETE (override) : suppression d'une catégorie et de son contenu
    $app->delete("/todolist/categories/{id}", function($id) use ($app){
        $category = Category::find($id);
        $category->delete();
        return $app['twig']->render('index.html.twig', array('categories' => Category::getAll()));
    });

    // Envoi vers le formulaire de modification d'une catégorie
    $app->get("/todolist/categories/{id}/edit", function($id) use ($app){
        $category = Category::find($id);
        return $app['twig']->render('category_edit.html.twig', array('category' => $category));
    });

    // PATCH (override) : validation du formulaire de modification d'un nom de catégorie
    $app->patch("/todolist/categories/{id}", function($id) use ($app){
        $name = $_POST['name'];
        $category = Category::find($id);
        $category->update($name);
        return $app['twig']->render('category.html.twig', array('category' => $category, 'tasks' => $category->getTasks()));
    });

    // Suppression d'une catégorie et de toutes les tâches associées
    $app->post("/todolist/delete_categories", function() use ($app){
        Category::deleteAll();
        return $app['twig']->render('index.html.twig');
    });

    // Suppression des tâches d'une catégorie
    $app->post("/todolist/delete_tasks", function() use ($app){
        Task::deleteAll();
        return $app['twig']->render('index.html.twig');
    });

    $app->get('/login', function (Request $request) use ($app) {
    return $app['twig']->render('login.html.twig', array(
        'error' => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
})
->bind('login')
;

$app->post('/todolist/login', function (Request $request) use ($app) {
    return $app->redirect($app['url_generator']->generate('homepage'));
})
->bind('login_check')
;

$app->get('/todolist/logout', function (Request $request) use ($app) {
    return $app->redirect($app['url_generator']->generate('login'));
})
->bind('logout');

/*$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});*/

    return $app;    
?>