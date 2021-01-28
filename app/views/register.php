<?php $this->layout('template', ['title' => 'Register user']) ?>

<h1>Page register user</h1>
<?php echo flash()->display(); ?>

<?php
    if ($auth->isLoggedIn()) {
        echo 'User is signed in' . '<br>';
        echo '<p class="lead"><b>' . 'id: ' . $auth->getUserId() . '<br>';
        echo 'email: ' . $auth->getEmail() . '<br>';
        echo 'username: ' . $auth->getUsername() . '</b></p>';
    }
    else {
        echo 'User is not signed in yet';
    }
?>