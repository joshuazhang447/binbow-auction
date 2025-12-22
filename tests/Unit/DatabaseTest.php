<?php

namespace Tests\Unit;

use App\Lib\Database;
use PDOStatement;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Class DatabaseTest
 * @package Tests\Unit
 */

#[CoversFunction('__construct')]
class DatabaseTest extends TestCase {
	/* @var $dbConnection \App\Lib\Database */
	protected static $dbConnection;
	protected $mock;

	public function setUp() : void {
		self::$dbConnection = Database::getConnection();
		self::$dbConnection->sqlQuery("INSERT INTO `categories` (cat) VALUES('UNIT_TEST')");
		/* Until PHPUnit adds support for dynamic properties (#[AllowDynamicProperties]) in mocks, we can't use this:
		 * $this->mock = $this->getMockBuilder('Category')
			->disableOriginalConstructor()
			->disableOriginalClone()
			->getMock();*/
		$this->mock = new stdClass();
	}

	public function tearDown() : void {
		self::$dbConnection->sqlQuery("DELETE FROM `categories` WHERE cat = 'UNIT_TEST';");
		self::$dbConnection->sqlQuery("ALTER TABLE `categories` AUTO_INCREMENT = 1;");
	}


	public function testGetConnection() {
		$this->assertInstanceOf(Database::class, self::$dbConnection);
	}


	public function testSqlQuery() {
		$result = self::$dbConnection->sqlQuery("SELECT * FROM `categories` WHERE cat = :cat", ["cat" => "UNIT_TEST"], true);
		$this->assertInstanceOf(PDOStatement::class, $result);

		$result = self::$dbConnection->sqlQuery("SELECT * FROM `categories` WHERE cat = :cat", ["cat" => "UNIT_TEST"]);
		$this->assertTrue($result);

		$result = self::$dbConnection->sqlQuery("SELECT * FROM `categories` WHERE cat = 'UNIT_TEST'");
		$this->assertTrue($result);
	}


	public function testFetch() {
		$result = self::$dbConnection->fetch("SELECT * FROM `categories` WHERE cat = :cat", get_class($this->mock), ["cat" => "UNIT_TEST"]);
		$this->assertIsArray($result);
		$this->assertNotEmpty($result);
		$first = $result[0];
		$this->assertInstanceOf(get_class($this->mock), $first);
	}


	public function testRowCount() {
		$result = self::$dbConnection->rowCount("SELECT * FROM `categories`;");
		$this->assertGreaterThanOrEqual(1, $result);
	}


	public function testLastInsertId() {
		self::$dbConnection->sqlQuery("INSERT INTO `categories` (cat) VALUES('UNIT_TEST')");
		$result = self::$dbConnection->lastInsertId();
		$this->assertGreaterThanOrEqual(1, $result);
	}

}
