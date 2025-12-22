<?php

namespace App\Lib;

use App\Models\User;
use Exception;
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
            if (session_status() === PHP_SESSION_ACTIVE) {
                throw new \Exception("Session already active");
            }
        } catch (Exception $e) {
            Logger::getLogger()->critical("Session creation error: ", ['exception' => $e]);
            die();
        }

        session_start();

        if (isset($_SESSION['user'])) {
            $this->user = $_SESSION['user'];
        }
    }

    public function __destruct()
    {
        session_write_close();
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
        session_destroy();
        return true;
    }


    public function open(string $path, string $name): bool
    {
        try {
            self::$dbConnection = new PDO(
                "mysql:host=" . CONFIG_DB_HOST . ";dbname=" . CONFIG_DB_NAME,
                CONFIG_DB_USER,
                CONFIG_DB_PASS
            );
        } catch (PDOException $e) {
            Logger::getLogger()->critical("could not create DB connection: ", ['exception' => $e]);
            die();
        }

        if (isset(self::$dbConnection)) {
            return true;
        }
        return false;
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
            Logger::getLogger()->critical("could not execute query: ", ['exception' => $e]);
            die();
        }
    }

    public function write(string $id, string $data): bool
    {
        try {
            $sql = "REPLACE INTO `sessions` (id, data) VALUES (:id, :data)";
            $statement = self::$dbConnection->prepare($sql);
            $result = $statement->execute(compact("id", "data"));
            return $result;
        } catch (PDOException $e) {
            Logger::getLogger()->critical("could not execute query: ", ['exception' => $e]);
            die();
        }
    }

    public function destroy(string $id): bool
    {
        try {
            $sql = "DELETE FROM `sessions` WHERE id = :id";
            $statement = self::$dbConnection->prepare($sql);
            $result = $statement->execute(compact("id"));
            return $result;
        } catch (PDOException $e) {
            Logger::getLogger()->critical("could not execute query: ", ['exception' => $e]);
            die();
        }
    }

    public function gc(int $max_lifetime): int|false
    {
        try {
            $sql = "DELETE FROM `sessions` WHERE DATE_ADD(last_accessed, INTERVAL $max_lifetime SECOND) < NOW()";
            $statement = self::$dbConnection->prepare($sql);
            $result = $statement->execute();
            return $result ? $statement->rowCount() : false;
        } catch (PDOException $e) {
            Logger::getLogger()->error("could not execute query: ", ['exception' => $e]);
            die();
        }
    }
}