<?php
class District
{
    private $conn;
    private $table = 'districts';

    public $id;
    public $province_id;
    public $name_en;
    public $name_si;
    public $name_ta;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public static function createTable($pdo)
    {
        try {
            $sql = "
                CREATE TABLE IF NOT EXISTS `districts` (
                 `id` int(11) NOT NULL AUTO_INCREMENT,
                 `province_id` int(11) NOT NULL,
                 `name_en` varchar(45) DEFAULT NULL,
                 `name_si` varchar(45) DEFAULT NULL,
                 `name_ta` varchar(45) DEFAULT NULL,
                 PRIMARY KEY (`id`),
                 KEY `provinces_id` (`province_id`)
                ) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
            ";
            $pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating districts table: " . $e->getMessage());
        }
    }

    public function getAll()
    {
        $query = 'SELECT * FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getByProvinceId($province_id)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE province_id = :province_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':province_id', $province_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $query = 'INSERT INTO ' . $this->table . ' (province_id, name_en, name_si, name_ta) VALUES (:province_id, :name_en, :name_si, :name_ta)';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':province_id', $data['province_id']);
        $stmt->bindParam(':name_en', $data['name_en']);
        $stmt->bindParam(':name_si', $data['name_si']);
        $stmt->bindParam(':name_ta', $data['name_ta']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $data)
    {
        $query = 'UPDATE ' . $this->table . ' SET province_id = :province_id, name_en = :name_en, name_si = :name_si, name_ta = :name_ta WHERE id = :id';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':province_id', $data['province_id']);
        $stmt->bindParam(':name_en', $data['name_en']);
        $stmt->bindParam(':name_si', $data['name_si']);
        $stmt->bindParam(':name_ta', $data['name_ta']);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>