<?php

class Province
{
    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS provinces (\n            id INT AUTO_INCREMENT PRIMARY KEY,\n            name VARCHAR(255) NOT NULL,\n            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            deleted_at TIMESTAMP NULL\n        );";

        try {
            $db->exec($query);
            echo "Table 'provinces' created successfully." . PHP_EOL;
        } catch (PDOException $e) {
            echo "Error creating table 'provinces': " . $e->getMessage() . PHP_EOL;
        }
    }

    public static function seed($db)
    {
        $query = "SELECT COUNT(*) FROM provinces";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            $provinces = [
                ['name' => 'Central Province'],
                ['name' => 'Eastern Province'],
                ['name' => 'North Central Province'],
                ['name' => 'Northern Province'],
                ['name' => 'North Western Province'],
                ['name' => 'Sabaragamuwa Province'],
                ['name' => 'Southern Province'],
                ['name' => 'Uva Province'],
                ['name' => 'Western Province'],
            ];

            $query = "INSERT INTO provinces (name) VALUES (:name)";
            $stmt = $db->prepare($query);

            foreach ($provinces as $province) {
                $stmt->bindValue(':name', $province['name']);
                $stmt->execute();
            }
            echo "Seeded 'provinces' table." . PHP_EOL;
        }
    }

    public static function dropTable($db)
    {
        $query = "DROP TABLE IF EXISTS provinces";

        try {
            $db->exec($query);
            echo "Table 'provinces' dropped successfully." . PHP_EOL;
        } catch (PDOException $e) {
            echo "Error dropping table 'provinces': " . $e->getMessage() . PHP_EOL;
        }
    }
}
