<?php

namespace App\Lib;

use App\Models\User;
use PDO;
use PDOException;
use SessionHandlerInterface;

class Session implements SessionHandlerInterface
{
    private $user = false;
    private static $dbConnection;

    public function __construct()
    {
        session_set_save_handler($this, true);

        try {
            $this->checkSessionExists();
        } catch (\Exception $e) {
            error_log("Session creation error: " . $e->getMessage());
            die();
        }

        session_start();

        if (isset($_SESSION['user'])) {
            $this->user = $_SESSION['user'];
        }
    }

    public function __destruct()
    {
        @session_write_close();
    }

    public function checkSessionExists()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            throw new \Exception("Session already active");
        }
    }

    public function isLoggedIn()
    {
        return $this->user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function login(User $userObj): bool
    {
        $this->user = $userObj;
        $_SESSION['user'] = $userObj;
        return true;
    }

    public function logout(): bool
    {
        $this->user = false;
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        return true;
    }

    public function open(string $path, string $name): bool
    {
        try {
            self::$dbConnection = new PDO(
                'mysql:host=' . \DB_HOST . ';dbname=' . \DB_NAME,
                \DB_USER,
                \DB_PASSWORD
            );
            self::$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("could not create DB connection: " . $e->getMessage());
            die();
        }

        return isset(self::$dbConnection);
    }

    public function close(): bool
    {
        self::$dbConnection = null;
        return true;
    }

    public function read(string $id): string|false
    {
        try {
            $sql = "SELECT data FROM `sessions` WHERE id = :id";
            $statement = self::$dbConnection->prepare($sql);
            $statement->execute(['id' => $id]);

            if ($statement->rowCount() == 1) {
                $result = $statement->fetch(PDO::FETCH_ASSOC);
                return $result['data'];
            } else {
                return "";
            }
        } catch (PDOException $e) {
            error_log("could not execute query: " . $e->getMessage());
            die();
        }
    }

    public function write(string $id, string $data): bool
    {
        try {
            $sql = "REPLACE INTO `sessions` (id, data, last_accessed) VALUES (:id, :data, NOW())";
            $statement = self::$dbConnection->prepare($sql);
            return $statement->execute(['id' => $id, 'data' => $data]);
        } catch (PDOException $e) {
            error_log("could not execute query: " . $e->getMessage());
            die();
        }
    }

    public function destroy(string $id): bool
    {
        try {
            $sql = "DELETE FROM `sessions` WHERE id = :id";
            $statement = self::$dbConnection->prepare($sql);
            return $statement->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("could not execute query: " . $e->getMessage());
            die();
        }
    }

    public function gc(int $max_lifetime): int|false
    {
        try {
            $sql = "DELETE FROM `sessions` WHERE DATE_ADD(last_accessed, INTERVAL :max_lifetime SECOND) < NOW()";
            $statement = self::$dbConnection->prepare($sql);
            $statement->bindValue(':max_lifetime', $max_lifetime, PDO::PARAM_INT);
            $result = $statement->execute();

            return $result ? $statement->rowCount() : false;
        } catch (PDOException $e) {
            error_log("could not execute query: " . $e->getMessage());
            die();
        }
    }
}