<?php
include 'connect.php';

if(isset($_POST['id'])){
    $id = intval($_POST['id']);

    // Join both tables
    $sql = "SELECT u.id, u.names, u.age, c.car, c.area 
            FROM users u 
            LEFT JOIN user_details c ON u.id = c.user_id 
            WHERE u.id = $id";

    $result = $conn->query($sql);

    if($result->num_rows > 0){
        $data = [];
        while($row = $result->fetch_assoc()){
            $data['names'] = $row['names'];
            $data['age'] = $row['age'];
            $data['details'][] = [
                'car' => $row['car'],
                'area' => $row['area']
            ];
        }

        echo json_encode([
            "status" => "success",
            "data" => $data
        ]);

    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No user found"
        ]);
    }
}
?>