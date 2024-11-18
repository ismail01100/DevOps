<?php
session_start();

// Include controllers
require_once 'Controller/UserController.php';
require_once 'Controller/ChargesController.php';
require_once 'Controller/PortefeuilleController.php';

// Get controller and action from URL
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'user';
$action = isset($_GET['action']) ? $_GET['action'] : 'login';

// Route to appropriate controller
switch($controller) {
    case 'user':
        $controller = new UserController();
        if($action == 'login') {
            if(isset($_SESSION['user'])) {
                header('Location: index.php?controller=portefeuille&action=index');
                exit();
            }
            if($_SERVER['REQUEST_METHOD'] == 'POST') {
                $controller->login($_POST['email'], $_POST['password']);
            } else {
                require 'View/user/login.php';
            }
        } elseif($action == 'register') {
            if($_SERVER['REQUEST_METHOD'] == 'POST') {
                $controller->register($_POST);
            } else {
                require 'View/user/register.php';
            }
        }
        break;
        
    case 'charges':
        $controller = new ChargesController();
        if($action == 'index') {
            $controller->index();
        } elseif($action == 'create') {
            if($_SERVER['REQUEST_METHOD'] == 'POST') {
                $controller->create($_POST);
            } else {
                require 'View/charges/add.php';
            }
        }
         elseif($action == 'delete') {
            $controller->delete($_GET['id']);
        }
        break;
        
    case 'portefeuille':
        $controller = new PortefeuilleController();
        if($action == 'index') {
            $controller->index();
        } elseif($action == 'updateSalary' && $_SERVER['REQUEST_METHOD'] == 'POST') {
            $controller->updateSalary($_POST);
        } elseif($action == 'addIncome' && $_SERVER['REQUEST_METHOD'] == 'POST') {
            $controller->addIncome($_POST);
        } elseif($action == 'resetBalance' && $_SERVER['REQUEST_METHOD'] == 'POST') {
            $controller->resetBalance($_POST);
        } elseif($action == 'updateSavingPourcentage' && $_SERVER['REQUEST_METHOD'] == 'POST') {
            $controller->updateSavingPourcentage($_POST);
        }
        break;
}
?>