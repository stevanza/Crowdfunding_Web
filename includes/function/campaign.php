<?php
// includes/functions/campaign.php
require_once '../config/database.php';

function createCampaign($userId, $title, $description, $targetAmount, $startDate, $endDate, $category, $image = null) {
    $conn = connectDB();
    
    // Validasi input
    if (empty($title) || empty($description) || empty($targetAmount)) {
        return ["success" => false, "message" => "Semua field harus diisi"];
    }
    
    // Escape string
    $title = escapeString($conn, $title);
    $description = escapeString($conn, $description);
    $category = escapeString($conn, $category);
    $image = escapeString($conn, $image);
    
    $sql = "INSERT INTO campaigns (user_id, title, description, target_amount, start_date, end_date, category, image) 
            VALUES ('$userId', '$title', '$description', '$targetAmount', '$startDate', '$endDate', '$category', '$image')";
    
    if ($conn->query($sql)) {
        $campaignId = $conn->insert_id;
        closeDB($conn);
        return ["success" => true, "message" => "Kampanye berhasil dibuat", "campaign_id" => $campaignId];
    } else {
        closeDB($conn);
        return ["success" => false, "message" => "Error: " . $sql . "<br>" . $conn->error];
    }
}

function updateCampaign($campaignId, $title, $description, $targetAmount, $endDate, $category, $image = null) {
    $conn = connectDB();
    
    $title = escapeString($conn, $title);
    $description = escapeString($conn, $description);
    $category = escapeString($conn, $category);
    
    $sql = "UPDATE campaigns 
            SET title = '$title', 
                description = '$description', 
                target_amount = '$targetAmount', 
                end_date = '$endDate', 
                category = '$category'";
    
    if ($image) {
        $image = escapeString($conn, $image);
        $sql .= ", image = '$image'";
    }
    
    $sql .= " WHERE campaign_id = '$campaignId'";
    
    if ($conn->query($sql)) {
        closeDB($conn);
        return ["success" => true, "message" => "Kampanye berhasil diupdate"];
    } else {
        closeDB($conn);
        return ["success" => false, "message" => "Error: " . $sql . "<br>" . $conn->error];
    }
}

function getCampaign($campaignId) {
    $conn = connectDB();
    
    $sql = "SELECT c.*, u.username, u.full_name 
            FROM campaigns c 
            JOIN users u ON c.user_id = u.user_id 
            WHERE c.campaign_id = '$campaignId'";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $campaign = $result->fetch_assoc();
        closeDB($conn);
        return $campaign;
    }
    
    closeDB($conn);
    return null;
}

function listCampaigns($category = null, $status = null, $limit = 10, $offset = 0) {
    $conn = connectDB();
    
    $sql = "SELECT c.*, u.username, u.full_name,
            (SELECT COUNT(*) FROM donations d WHERE d.campaign_id = c.campaign_id) as donors_count
            FROM campaigns c 
            JOIN users u ON c.user_id = u.user_id 
            WHERE 1=1";
    
    if ($category) {
        $category = escapeString($conn, $category);
        $sql .= " AND c.category = '$category'";
    }
    
    if ($status) {
        $status = escapeString($conn, $status);
        $sql .= " AND c.status = '$status'";
    }
    
    $sql .= " ORDER BY c.created_at DESC LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($sql);
    $campaigns = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $campaigns[] = $row;
        }
    }
    
    closeDB($conn);
    return $campaigns;
}

function uploadCampaignImage($file) {
    $targetDir = "../../assets/uploads/campaigns/";
    $fileName = time() . '_' . basename($file["name"]);
    $targetFile = $targetDir . $fileName;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));
    
    // Check if image file is actual image
    if(isset($_POST["submit"])) {
        $check = getimagesize($file["tmp_name"]);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            return ["success" => false, "message" => "File bukan gambar."];
        }
    }
    
    // Check file size
    if ($file["size"] > 500000) {
        return ["success" => false, "message" => "Ukuran file terlalu besar."];
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        return ["success" => false, "message" => "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan."];
    }
    
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return ["success" => true, "filename" => $fileName];
    } else {
        return ["success" => false, "message" => "Gagal mengupload file."];
    }
}