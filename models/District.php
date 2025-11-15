<?php

class District
{
    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS districts (\n            id INT AUTO_INCREMENT PRIMARY KEY,\n            name VARCHAR(255) NOT NULL,\n            province_id INT NOT NULL,\n            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            deleted_at TIMESTAMP NULL,\n            FOREIGN KEY (province_id) REFERENCES provinces(id)\n        );";

        try {
            $db->exec($query);
            echo "Table 'districts' created successfully." . PHP_EOL;
        } catch (PDOException $e) {
            echo "Error creating table 'districts': " . $e->getMessage() . PHP_EOL;
        }
    }

    public static function seed($db)
    {
        $query = "SELECT COUNT(*) FROM districts";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            $districts = [
                ['name' => 'Kandy', 'province_id' => 1],
                ['name' => 'Matale', 'province_id' => 1],
                ['name' => 'Nuwara Eliya', 'province_id' => 1],
                ['name' => 'Ampara', 'province_id' => 2],
                ['name' => 'Batticaloa', 'province_id' => 2],
                ['name' => 'Trincomalee', 'province_id' => 2],
                ['name' => 'Anuradhapura', 'province_id' => 3],
                ['name' => 'Polonnaruwa', 'province_id' => 3],
                ['name' => 'Jaffna', 'province_id' => 4],
                ['name' => 'Kilinochchi', 'province_id' => 4],
                ['name' => 'Mannar', 'province_id' => 4],
                ['name' => 'Mullaitivu', 'province_id' => 4],
                ['name' => 'Vavuniya', 'province_id' => 4],
                ['name' => 'Kurunegala', 'province_id' => 5],
                ['name' => 'Puttalam', 'province_id' => 5],
                ['name' => 'Kegalle', 'province_id' => 6],
                ['name' => 'Ratnapura', 'province_id' => 6],
                ['name' => 'Galle', 'province_id' => 7],
                ['name' => 'Hambantota', 'province_id' => 7],
                ['name' => 'Matara', 'province_id' => 7],
                ['name' => 'Badulla', 'province_id' => 8],
                ['name' => 'Monaragala', 'province_id' => 8],
                ['name' => 'Colombo', 'province_id' => 9],
                ['name' => 'Gampaha', 'province_id' => 9],
                ['name' => 'Kalutara', 'province_id' => 9],
            ];

            $query = "INSERT INTO districts (name, province_id) VALUES (:name, :province_id)";
            $stmt = $db->prepare($query);

            foreach ($districts as $district) {
                $stmt->bindValue(':name', $district['name']);
                $stmt->bindValue(':province_id', $district['province_id']);
                $stmt->execute();
            }
            echo "Seeded 'districts' table." . PHP_EOL;
        }
    }

    public static function dropTable($db)
    {
        $query = "DROP TABLE IF EXISTS districts";

        try {
            $db->exec($query);
            echo "Table 'districts' dropped successfully." . PHP_EOL;
        } catch (PDOException $e) {
            echo "Error dropping table 'districts': " . $e->getMessage() . PHP_EOL;
        }
    }
}
