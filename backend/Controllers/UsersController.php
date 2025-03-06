<?php

require_once "./Core/Controller.php";

class UsersController extends Controller
{
    public const ROUTES = [
        "users.login" => ['params' => ['email', 'password']],
        "users.logout" => ['user_only' => true, 'params' => []],
        "users.create" => ['params' => ['email', 'password', 'username']],
        "users.getLoggedUser" => ['user_only' => true, 'params' => []],
    ];

    public function create(array $request): void
    {
        $email = $request['email'];
        $password = $request['password'];
        $username = $request['username'];

        if (false == filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['result' => 'error', 'message' => 'Invalid Email.']);
        }

        if (false == empty($this->db->select("SELECT id FROM users WHERE email = \"$email\""))) {
            $this->jsonResponse(['result' => 'error', 'message' => 'Email already in use.']);
        }

        $userId = $this->db->insert('users', [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'email' => $email,
        ]);

        $_SESSION['userId'] = $userId;
        $_SESSION['username'] = $username;

        $this->jsonResponse(['result' => 'success', [
            'userId' => $userId,
            'username' => $username,
        ]]);
    }

    public function login(array $request): void
    {
        $email = $request['email'];
        $password = $request['password'];

        $user = $this->db->selectOne("SELECT id, username, password FROM users WHERE email = '{$email}'");

        if (empty($user)) {
            $this->jsonResponse(['result' => 'error', 'message' => 'Wrong credentials.']);
        }

        if (password_verify($password, $user['password'])) {
            $_SESSION['userId'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];

            $this->jsonResponse(['result' => 'success', [
                'userId' => $_SESSION['userId'],
                'username' => $_SESSION['username'],
            ]]);
        }

        $this->jsonResponse(['result' => 'error', 'message' => 'Wrong credentials.']);
    }

    public function logout(array $request) {
        unset($_SESSION['userId']);
        unset($_SESSION['username']);
        $this->jsonResponse(['result' => 'success', 'message' => "Logged out."]);
    }

    public function getLoggedUser(array $request): void
    {
        $this->jsonResponse(['result' => 'success', [
            'userId' => $_SESSION['userId'],
            'username' => $_SESSION['username'],
        ]]);
    }
}
