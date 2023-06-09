<?php

namespace Controller;

use Model\Connect; // "use" pour accéder à la classe Connect située dans le namespace "Model"


class AuthentificationController
{
    /* Connexion */
    public function login()
    {
        if (isset($_SESSION['users'])) {
            $_SESSION['message'] = "Vous êtes déjà connecté";
            header("Location: index.php?action=accueil");
            exit;
        }
        if (isset($_POST['login'])) {

            // Filtres
            $utilisateur = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            // FILTER_VALIDATE_EMAIL : permet la validation des adresses e-mail Unicode conformément aux normes (n'accepte que les caractères ASCII)
            // FILTER_FLAG_EMAIL_UNICODE : autorise l'utilisation de caractère non-ASCII (accent, caractères chinois, ...)
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if ($utilisateur !== false && $password !== false && !empty($utilisateur) && !empty($password)) {

                $pdo = Connect::seConnecter();

                $requeteUtilisateur = $pdo->prepare("
                    SELECT *
                    FROM users
                    WHERE email = :utilisateur OR username = :utilisateur
                ");
                $requeteUtilisateur->execute([
                    'utilisateur' => $utilisateur
                ]);

                // Vérifie si l'utilisateur existe
                if ($requeteUtilisateur->rowCount() === 1) {
                    $userData = $requeteUtilisateur->fetch();
                    $hashedPassword = $userData['password'];

                    // Vérifie le mot de passe
                    if (password_verify($password, $hashedPassword)) {
                        $userId = $userData['id'];
                        $_SESSION['message'] = "Connexion réussie";
                        $_SESSION['users'] = $userData;
                        header("Location: index.php?action=accueil");
                        exit;
                    } else {
                        $_SESSION['messageError'] = "Mot de passe incorrect";
                        header("Location: index.php?action=login");
                        exit;
                    }
                } else {
                    $_SESSION['messageError'] = "Nom d'utilisateur ou email incorrect";
                    header("Location: index.php?action=login");
                    exit;
                }
            }
        }

        require "view/login.php";
    }

    // Inscription
    public function register()
    {
        if (isset($_SESSION['users'])) {
            $_SESSION['message'] = "Vous êtes déjà connecté";
            header("Location: index.php?action=accueil");
            exit;
        }
        if (isset($_POST['register'])) {

            // Filtres
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $confirmPassword = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);


            if ($email !== false && $username !== false && $password !== false && $confirmPassword !== false && !empty($email) && !empty($username) && !empty($password) && !empty($confirmPassword)) {
                
                $pdo = Connect::seConnecter();

                // Vérifie si l'email existe déjà
                $requeteEmail = $pdo->prepare("
                    SELECT COUNT(*)
                    FROM users
                    WHERE email = :email
                ");
                $requeteEmail->execute([
                    'email' => $email
                ]);

                // J'utilise fetchColumn() pour vérifier si le nombre de ligne est supérieur à 0, si oui : l'email est déjà utilisé
                if ($requeteEmail->fetchColumn() > 0) {
                    $_SESSION['messageError'] = "Email déjà utilisé sur ce site";
                    header("Location: index.php?action=register");
                    exit;
                }

                // Vérifie si l'username exite déjà
                $requeteUsername = $pdo->prepare("
                    SELECT COUNT(*)
                    FROM users
                    WHERE username = :username
                ");
                $requeteUsername->execute([
                    'username' => $username
                ]);

                if ($requeteUsername->fetchColumn() > 0) {
                    $_SESSION['messageError'] = "Username déjà utilisé sur ce site";
                    header("Location: index.php?action=register");
                    exit;
                }

                // Vérifie que les 2 mots de passe correspondent
                if ($_POST['password'] !== $_POST['confirm_password']) {
                    $_SESSION['messageError'] = "Les mots de passe ne correspondent pas";
                    header("Location: index.php?action=register");
                    exit;
                  }

                // Hachage du mot de passe
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Ajout à la db
                $requeteAjoutUser = $pdo->prepare("
                  INSERT INTO users (username, password, email)
                  VALUES (:username, :password, :email)
                ");
                $requeteAjoutUser->execute([
                    'username' => $username,
                    'password' => $hashedPassword,
                    'email' => $email
                ]);

                // Redirection + message
                $_SESSION['message'] = "Inscription réussie !";
                header("Location: index.php?action=login");
                exit;

            } else {
                $_SESSION['messageError'] = "Erreur dans le formulaire";
                header("Location: index.php?action=register");
                exit;
            }
        }

        require "view/register.php";
    }

    // Déconnexion
    public function logout()
    {
        if (!isset($_SESSION['users'])) {
            $_SESSION['message'] = "Vous êtes déjà déconnecté";
            header("Location: index.php?action=accueil");
            exit;
        }
        if ($_GET['action'] === 'logout') {
            // Détruit la session et rediriger vers l'accueil
            unset($_SESSION['users']);
            $_SESSION['message'] = "Déconnexion réussie";
            header("Location: index.php?action=accueil");
            exit;
        }

    }

}
