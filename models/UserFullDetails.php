<?php

class UserFullDetails
{
    private $conn;
    private $table = 'user_full_details';

    public $id;
    public $student_number;
    public $civil_status;
    public $gender;
    public $address_line_1;
    public $address_line_2;
    public $city_id;
    public $telephone_1;
    public $telephone_2;
    public $nic;
    public $e_mail;
    public $birth_day;
    public $updated_by;
    public $updated_at;
    public $full_name;
    public $name_with_initials;
    public $name_on_certificate;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS `user_full_details` (\n            `id` int(50) NOT NULL AUTO_INCREMENT,\n            `student_number` varchar(50) DEFAULT NULL,\n            `civil_status` varchar(50) DEFAULT NULL,\n            `gender` varchar(10) DEFAULT NULL,\n            `address_line_1` varchar(255) DEFAULT NULL,\n            `address_line_2` varchar(255) DEFAULT NULL,\n            `city_id` int(11) DEFAULT NULL,\n            `telephone_1` varchar(10) DEFAULT NULL,\n            `telephone_2` varchar(10) DEFAULT NULL,\n            `nic` varchar(50) DEFAULT NULL,\n            `e_mail` varchar(255) DEFAULT NULL,\n            `birth_day` date DEFAULT NULL,\n            `updated_by` varchar(50) DEFAULT NULL,\n            `updated_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6),\n            `full_name` text DEFAULT NULL,\n            `name_with_initials` text DEFAULT NULL,\n            `name_on_certificate` text DEFAULT NULL,\n            PRIMARY KEY (`id`)\n        ) ENGINE=MyISAM AUTO_INCREMENT=5542 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Table Creation Error (UserFullDetails): " . $e->getMessage());
        }
    }

    public function read()
    {
        $query = 'SELECT * FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function read_single($id)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function read_by_student_number($student_number)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE student_number = :student_number';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_number', $student_number);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $query = 'INSERT INTO ' . $this->table . ' (student_number, civil_status, gender, address_line_1, address_line_2, city_id, telephone_1, telephone_2, nic, e_mail, birth_day, updated_by, full_name, name_with_initials, name_on_certificate) VALUES (:student_number, :civil_status, :gender, :address_line_1, :address_line_2, :city_id, :telephone_1, :telephone_2, :nic, :e_mail, :birth_day, :updated_by, :full_name, :name_with_initials, :name_on_certificate)';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':student_number', $data['student_number']);
        $stmt->bindParam(':civil_status', $data['civil_status']);
        $stmt->bindParam(':gender', $data['gender']);
        $stmt->bindParam(':address_line_1', $data['address_line_1']);
        $stmt->bindParam(':address_line_2', $data['address_line_2']);
        $stmt->bindParam(':city_id', $data['city_id']);
        $stmt->bindParam(':telephone_1', $data['telephone_1']);
        $stmt->bindParam(':telephone_2', $data['telephone_2']);
        $stmt->bindParam(':nic', $data['nic']);
        $stmt->bindParam(':e_mail', $data['e_mail']);
        $stmt->bindParam(':birth_day', $data['birth_day']);
        $stmt->bindParam(':updated_by', $data['updated_by']);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':name_with_initials', $data['name_with_initials']);
        $stmt->bindParam(':name_on_certificate', $data['name_on_certificate']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];
        $allowed_fields = ['student_number', 'civil_status', 'gender', 'address_line_1', 'address_line_2', 'city_id', 'telephone_1', 'telephone_2', 'nic', 'e_mail', 'birth_day', 'updated_by', 'full_name', 'name_with_initials', 'name_on_certificate'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_fields)) {
                $fields[] = "`$key` = :$key";
                $params[":$key"] = htmlspecialchars(strip_tags($value));
            }
        }

        if (empty($fields)) {
            return false;
        }

        $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE `id` = :id";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute($params);
    }
    
    public function updateByStudentNumber($studentNumber, $data)
    {
        $fields = [];
        $params = [':student_number' => $studentNumber];
        $allowed_fields = ['civil_status', 'gender', 'address_line_1', 'address_line_2', 'city_id', 'telephone_1', 'telephone_2', 'nic', 'e_mail', 'birth_day', 'updated_by', 'full_name', 'name_with_initials', 'name_on_certificate'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_fields)) {
                $fields[] = "`$key` = :$key";
                $params[":$key"] = htmlspecialchars(strip_tags($value));
            }
        }

        if (empty($fields)) {
            return true; // Nothing to update
        }

        $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE `student_number` = :student_number";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute($params);
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
