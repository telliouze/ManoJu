<?php
    require_once __DIR__."/../vendor/autoload.php";
    require_once __DIR__."/../src/Task.php";

    session_start();
    if(empty($_SESSION['list_of_tasks'])){
        $_SESSION['list_of_tasks'] = array();
    }

    $app = new Silex\Application();
    $app->register(new Silex\Provider\TwigServiceProvider(),array('twig.path' => __DIR__.'/../views'));

    $app->get("/", function() use ($app) {
        /*$test_task = new Task("Learn PHP.");
        $another_test_task = new Task("Learn Drupal.");
        $third_task = new Task("Visit France.");

        $list_of_tasks = array($test_task,$another_test_task,$third_task);*/

        /*foreach($list_of_tasks as $task)
        {
            $output = $output."<p>".$task->getDescription()."</p>";
        }*/

        //return $output;
        return $app['twig']->render('tasks.html.twig', array('tasks'=>Task::getAll()));
    });

    $app->post("/tasks", function() use ($app) {
        $task = new Task($_POST['description']);
        $task->save();
        return $app['twig']->render('create_task.html.twig', array('newtask'=>$task));
    });

    $app->post("/delete_tasks", function() use ($app){
        Task::deleteAll();
        return $app['twig']->render('delete_tasks.html.twig');
    });

    return $app;
    
?>