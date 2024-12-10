<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

include 'connect.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['user_id']) || !isset($data['gacha_id']) || !isset($data['pull_count'])) {
    echo json_encode(['error' => 'Missing required data']);
    exit();
}

$user_id = $data['user_id'];
$gacha_id = $data['gacha_id'];
$pull_count = $data['pull_count']; // 1 or 10

try {
    // Start transaction
    $conn->beginTransaction();

    // Get all possible rewards for this gacha
    $rewardsQuery = "SELECT * FROM gacha_rewards WHERE gacha_id = :gacha_id";
    $rewardsStmt = $conn->prepare($rewardsQuery);
    $rewardsStmt->bindParam(':gacha_id', $gacha_id, PDO::PARAM_INT);
    $rewardsStmt->execute();
    $rewards = $rewardsStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rewards)) {
        throw new Exception('No rewards available for this gacha');
    }

    $pulls = [];
    
    // Perform pulls
    for ($i = 0; $i < $pull_count; $i++) {
        // Random number between 1 and 100 for percentage
        $rand = mt_rand(1, 100);
        
        // Determine rarity based on random number
        // Example odds:
        // 1-60: Common (60%)
        // 61-90: Rare (30%)
        // 91-100: Super Rare (10%)
        $rarity = 'common';
        if ($rand > 90) {
            $rarity = 'super_rare';
        } elseif ($rand > 60) {
            $rarity = 'rare';
        }

        // Filter rewards by rarity and select random one
        $filtered_rewards = array_filter($rewards, function($reward) use ($rarity) {
            // You might want to add a rarity column to your gacha_rewards table
            // For now, we'll just randomly select from all rewards
            return true;
        });

        // Select random reward from filtered list
        $reward = $filtered_rewards[array_rand($filtered_rewards)];

        // Record the pull in history
        $historyQuery = "INSERT INTO user_gacha_history (user_id, gacha_id, pulled_at) 
                        VALUES (:user_id, :gacha_id, NOW())";
        $historyStmt = $conn->prepare($historyQuery);
        $historyStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $historyStmt->bindParam(':gacha_id', $gacha_id, PDO::PARAM_INT);
        $historyStmt->execute();

        // Add pull result to array
        $pulls[] = [
            'reward' => $reward,
            'rarity' => $rarity
        ];
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'pulls' => $pulls
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollBack();
    error_log("Pull error: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?> 