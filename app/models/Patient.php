<?php

class Patient {
    public static function all() {
        $db=Database::connect();
        $stmt=$db->query("SELECT * FROM patients");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$data || !is_array($data)) {
            return [];
        }
        foreach ($data as &$row) {
            $row['phone'] = Crypto::decrypt($row['phone']);
            $row['address'] = Crypto::decrypt($row['address']);
        }

        return $data;

    }
    public static function findById($id) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT id FROM patients WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
    $db = Database::connect();
    $stmt = $db->prepare(
        "INSERT INTO patients (name, age, gender, phone, address) VALUES (?, ?, ?, ?, ?)"
    );
    return $stmt->execute([
        $data['name'],
        $data['age'],
        $data['gender'],
        Crypto::encrypt($data['phone']),
        Crypto::encrypt($data['address'])
    ]);
}

    public static function update($id, $data) {
    $db = Database::connect();
    $stmt = $db->prepare(
        "UPDATE patients SET name=?, age=?, gender=?, phone=?, address=? WHERE id=?"
    );
    return $stmt->execute([
        $data['name'],
        $data['age'],
        $data['gender'],
        Crypto::encrypt($data['phone']),
        Crypto::encrypt($data['address']),
        $id
    ]);
    }

    public static function delete($id) {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM patients WHERE id=?");
        return $stmt->execute([$id]);
    }
}
