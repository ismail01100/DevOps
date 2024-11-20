<?php
session_start();

// Include controllers
require_once 'Controller/UserController.php';
require_once 'Controller/ChargesController.php';
require_once 'Controller/PortefeuilleController.php';

// Get controller and action from URL
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'user';
$action = isset($_GET['action']) ? $_GET['action'] : 'login';

// Verify user connection for protected controllers
if (($controller == 'charges' || $controller == 'portefeuille') && !isset($_SESSION['user']['CodeUtilisateur'])) {
    header('Location: index.php?controller=user&action=login');
    exit();
}

// Route to appropriate controller
switch ($controller) {
    case 'user':
        $controller = new UserController();
        if ($action == 'login') {
            if (isset($_SESSION['user']['CodeUtilisateur'])) {
                header('Location: index.php?controller=portefeuille&action=index');
                exit();
            }
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $controller->login($_POST['email'], $_POST['password']);
            } else {
                require 'View/user/login.php';
            }
        } elseif ($action == 'register') {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $controller->register($_POST);
            } else {
                require 'View/user/register.php';
            }
        } elseif ($action == 'logout') {
            $controller->logout();
        }
        break;

    case 'charges':
        $controller = new ChargesController();
        if ($action == 'index') {
            $controller->index();
        } elseif ($action == 'create') {
            $controller->create($_POST);
        } elseif ($action == 'delete') {
            $controller->delete($_POST);
        } elseif ($action == 'edit') {
            $controller->edit($_GET['id']);
        } elseif ($action == 'update') {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $controller->update($_POST);
            }
        } elseif ($action == 'get') {
            $controller->get($_GET['id']);
        }
        break;

    case 'portefeuille':
        $controller = new PortefeuilleController();
        if ($action == 'index') {
            $controller->index();
        } elseif ($action == 'settings') {
            $controller->settings();
        } elseif ($action == 'updateSalary' && $_SERVER['REQUEST_METHOD'] == 'POST') {
            $controller->updateSalary($_POST);
        } elseif ($action == 'addIncome' && $_SERVER['REQUEST_METHOD'] == 'POST') {
            $controller->addIncome($_POST);
        } elseif ($action == 'resetBalance' && $_SERVER['REQUEST_METHOD'] == 'POST') {
            $controller->resetBalance();
        } elseif ($action == 'updateSavingPourcentage' && $_SERVER['REQUEST_METHOD'] == 'POST') {
            $controller->updateSavingPourcentage($_POST);
        }
        break;
}
?>