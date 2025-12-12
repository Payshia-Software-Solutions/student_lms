<?php
class City
{
    private $conn;
    private $table = 'cities';

    public $id;
    public $district_id;
    public $name_en;
    public $name_si;
    public $name_ta;
    public $sub_name_en;
    public $sub_name_si;
    public $sub_name_ta;
    public $postcode;
    public $latitude;
    public $longitude;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public static function createTable($pdo)
    {
        try {
            $sql = "
                CREATE TABLE IF NOT EXISTS `cities` (
                 `id` int(11) NOT NULL AUTO_INCREMENT,
                 `district_id` int(11) NOT NULL,
                 `name_en` varchar(45) DEFAULT NULL,
                 `name_si` varchar(45) DEFAULT NULL,
                 `name_ta` varchar(45) DEFAULT NULL,
                 `sub_name_en` varchar(45) DEFAULT NULL,
                 `sub_name_si` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_sinhala_ci DEFAULT NULL,
                 `sub_name_ta` varchar(45) DEFAULT NULL,
                 `postcode` varchar(15) DEFAULT NULL,
                 `latitude` double DEFAULT NULL,
                 `longitude` double DEFAULT NULL,
                 PRIMARY KEY (`id`),
                 KEY `fk_cities_districts1_idx` (`district_id`)
                ) ENGINE=MyISAM AUTO_INCREMENT=1863 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
            ";
            $pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating cities table: " . $e->getMessage());
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
    
    public function getByDistrictId($district_id)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE district_id = :district_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':district_id', $district_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $query = 'INSERT INTO ' . $this->table . ' (district_id, name_en, name_si, name_ta, sub_name_en, sub_name_si, sub_name_ta, postcode, latitude, longitude) VALUES (:district_id, :name_en, :name_si, :name_ta, :sub_name_en, :sub_name_si, :sub_name_ta, :postcode, :latitude, :longitude)';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':district_id', $data['district_id']);
        $stmt->bindParam(':name_en', $data['name_en']);
        $stmt->bindParam(':name_si', $data['name_si']);
        $stmt->bindParam(':name_ta', $data['name_ta']);
        $stmt->bindParam(':sub_name_en', $data['sub_name_en']);
        $stmt->bindParam(':sub_name_si', $data['sub_name_si']);
        $stmt->bindParam(':sub_name_ta', $data['sub_name_ta']);
        $stmt->bindParam(':postcode', $data['postcode']);
        $stmt->bindParam(':latitude', $data['latitude']);
        $stmt->bindParam(':longitude', $data['longitude']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $data)
    {
        $query = 'UPDATE ' . $this->table . ' SET district_id = :district_id, name_en = :name_en, name_si = :name_si, name_ta = :name_ta, sub_name_en = :sub_name_en, sub_name_si = :sub_name_si, sub_name_ta = :sub_name_ta, postcode = :postcode, latitude = :latitude, longitude = :longitude WHERE id = :id';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':district_id', $data['district_id']);
        $stmt->bindParam(':name_en', $data['name_en']);
        $stmt->bindParam(':name_si', $data['name_si']);
        $stmt->bindParam(':name_ta', $data['name_ta']);
        $stmt->bindParam(':sub_name_en', $data['sub_name_en']);
        $stmt->bindParam(':sub_name_si', $data['sub_name_si']);
        $stmt->bindParam(':sub_name_ta', $data['sub_name_ta']);
        $stmt->bindParam(':postcode', $data['postcode']);
        $stmt->bindParam(':latitude', $data['latitude']);
        $stmt->bindParam(':longitude', $data['longitude']);

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