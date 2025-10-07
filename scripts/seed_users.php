<?php
// scripts/seed_users.php
// Simple seeder to create two users using the same logic as RegisterController.

require_once __DIR__ . '/../app/Helpers/functions.php'; // for parseEnv()
require_once __DIR__ . '/../app/Config/BaseModel.php'; // for UserModel BaseModel
require_once __DIR__ . '/../app/Models/UserModel.php';

use Models\UserModel;

/**
 * Create a user like RegisterController:
 *  - insert user
 *  - insert identity (hashing handled in model)
 *  - assign group (organizer|customer)
 * Skips if user with the email already exists.
 */
function register_like(UserModel $uModel, string $name, string $email, string $password, string $group): ?int
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "[SKIP] Invalid email: $email\n";
        return null;
    }
    if (!in_array($group, ['organizer', 'customer'], true)) {
        echo "[SKIP] Invalid group: $group for $email\n";
        return null;
    }

    // If user already exists, skip
    try {
        $existing = $uModel->getUserByEmail($email);
        if ($existing && count($existing) > 0) {
            $uid = $existing[0]['id'] ?? null;
            echo "[OK] User already exists: $email (user_id=" . ($uid ?? '?') . ")\n";
            return $uid;
        }
    } catch (Exception $e) {
        // We still try to create
        echo "[ERR] ". $e->getMessage(). "\n";
    }

    $userId = null;
    $identityId = null;

    try {
        // Create base user
        $userId = $uModel->insertNewUser($name, $email);

        if (!$userId) {
            echo "[ERR] Failed to insert user for $email\n";
            exit(1);
        }

        // Create auth identity (model expected to hash $password)
        $identityId = $uModel->insertNewIdentity($userId, $email, $password);

        if (!$identityId) {
            echo "[ERR] Failed to insert identity for $email\n";
            exit(1);
        }

        // Assign group
        $uModel->insertNewUserGroup($userId, $group);

        echo "[OK] Created $group: $email (user_id=$userId)\n";
        return $userId;

    } catch (Exception $e) {
        // Rollback best-effort (mirrors RegisterController error handling)
        try {
            if ($userId) {
                // Delete identity if error occurred before completing registration
                $uModel->deleteUserIdentity($userId);
                $uModel->deleteUser($userId);
            }
        } catch (Exception $e1) {
            echo "[ERR] ". $e1->getMessage(). "\n";    
        }

        echo "[ERR] ". $e->getMessage(). "\n";
        return null;
    }
}

try {
    $uModel = new UserModel();
    parseEnvFile( __DIR__ . "/../.env");
    // Seed organizer
    register_like($uModel, 'User Organizer', 'organizer@test.com', 'test', 'organizer');

    // Seed customer
    register_like($uModel, 'User Customer', 'customer@test.com', 'test', 'customer');

    echo "[DONE] Seeding complete.\n";
} catch (Exception $e) {
    echo "[FATAL] " . $e->getMessage() . "\n";
    exit(1);
}
