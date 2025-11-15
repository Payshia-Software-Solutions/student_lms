<?php

class City
{
    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS cities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            district_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL,
            FOREIGN KEY (district_id) REFERENCES districts(id)
        );";

        try {
            $db->exec($query);
            echo "Table 'cities' created successfully." . PHP_EOL;
        } catch (PDOException $e) {
            echo "Error creating table 'cities': " . $e->getMessage() . PHP_EOL;
        }
    }

    public static function seed($db)
    {
        $query = "SELECT COUNT(*) FROM cities";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            $cities = [
                // Kandy District
                ['name' => 'Kandy', 'district_id' => 1],
                ['name' => 'Gampola', 'district_id' => 1],
                ['name' => 'Nawalapitiya', 'district_id' => 1],
                ['name' => 'Peradeniya', 'district_id' => 1],

                // Matale District
                ['name' => 'Matale', 'district_id' => 2],
                ['name' => 'Dambulla', 'district_id' => 2],
                ['name' => 'Sigiriya', 'district_id' => 2],

                // Nuwara Eliya District
                ['name' => 'Nuwara Eliya', 'district_id' => 3],
                ['name' => 'Hatton', 'district_id' => 3],
                ['name' => 'Nanu Oya', 'district_id' => 3],

                // Ampara District
                ['name' => 'Ampara', 'district_id' => 4],
                ['name' => 'Akkaraipattu', 'district_id' => 4],
                ['name' => 'Kalmunai', 'district_id' => 4],

                // Batticaloa District
                ['name' => 'Batticaloa', 'district_id' => 5],

                // Trincomalee District
                ['name' => 'Trincomalee', 'district_id' => 6],

                // Anuradhapura District
                ['name' => 'Anuradhapura', 'district_id' => 7],

                // Polonnaruwa District
                ['name' => 'Polonnaruwa', 'district_id' => 8],

                // Jaffna District
                ['name' => 'Jaffna', 'district_id' => 9],

                // Colombo District
                ['name' => 'Colombo', 'district_id' => 23],
                ['name' => 'Dehiwala-Mount Lavinia', 'district_id' => 23],
                ['name' => 'Moratuwa', 'district_id' => 23],
                ['name' => 'Sri Jayewardenepura Kotte', 'district_id' => 23],

                // Gampaha District
                ['name' => 'Gampaha', 'district_id' => 24],
                ['name' => 'Negombo', 'district_id' => 24],
                ['name' => 'Ja-Ela', 'district_id' => 24],

                // Kalutara District
                ['name' => 'Kalutara', 'district_id' => 25],
                ['name' => 'Panadura', 'district_id' => 25],
                ['name' => 'Horana', 'district_id' => 25],
            ];


            $query = "INSERT INTO cities (name, district_id) VALUES (:name, :district_id)";
            $stmt = $db->prepare($query);

            foreach ($cities as $city) {
                $stmt->bindValue(':name', $city['name']);
                $stmt->bindValue(':district_id', $city['district_id']);
                $stmt->execute();
            }
            echo "Seeded 'cities' table." . PHP_EOL;
        }
    }

    public static function dropTable($db)
    {
        $query = "DROP TABLE IF EXISTS cities";

        try {
            $db->exec($query);
            echo "Table 'cities' dropped successfully." . PHP_EOL;
        } catch (PDOException $e) {
            echo "Error dropping table 'cities': " . $e->getMessage() . PHP_EOL;
        }
    }
}
