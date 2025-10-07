<?php

namespace Models;

use Config\BaseModel;

/**
 * Class UserModel
 * 
 * Handles all database operations related to users, 
 * including authentication, groups, and login attempts.
 */
class UserModel extends BaseModel
{
    /**
     * Initialize table and primary key
     */
    public function __construct()
    {
        $this->table = "users";
        $this->primaryKey = "id";
    }

    /**
     * Fetch a user by their email
     * 
     * @param string $email
     * @return array|null
     */
    public function getUserByEmail(string $email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";

        return $this->select($sql, ['email' => $email]);
    }

    /**
     * Fetch a user's authentication identity from auth_identities_users table
     * 
     * @param int $userId
     * @param string $email
     * @return array|null
     */
    public function getUserAuthIdentity(int $userId, string $email)
    {
        $sql = "SELECT * FROM auth_identities_users WHERE user_id = :userId AND secret = :email LIMIT 1";

        return $this->select($sql, ['userId' => $userId, 'email' => $email]);
    }

    /**
     * Fetch all groups the user belongs to
     * 
     * @param int $userId
     * @return array
     */
    public function getUserGroup(int $userId)
    {
        $sql = "SELECT * FROM auth_groups_users WHERE user_id = :userId";

        return $this->select($sql, ['userId' => $userId]);
    }

    /**
     * Insert a new user into the users table
     * 
     * @param string $name
     * @param string $email
     * @return int|false Inserted ID or false on failure
     */
    public function insertNewUser(string $name, string $email)
    {
        $sql = "INSERT INTO {$this->table}(name, email) VALUES (:namee, :email)";

        return $this->insert($sql, ['namee' => $name, 'email' => $email]);
    }

    /**
     * Insert a new identity record for the user
     * 
     * @param string $userId
     * @param string $secret
     * @param string $secret2 Plain password to be hashed
     * @return int|false Inserted ID or false
     */
    public function insertNewIdentity(string $userId, string $secret, string $secret2)
    {
        $sql = "INSERT INTO auth_identities_users (user_id, secret, secret2) VALUES (:userId, :secret, :secret2)";

        return $this->insert($sql, [
            'userId' => $userId, 
            'secret' => $secret, 
            'secret2' => password_hash($secret2, PASSWORD_DEFAULT)
        ]);
    }

    /**
     * Insert a new group association for the user
     * 
     * @param int $userId
     * @param string $group
     * @return int|false
     */
    public function insertNewUserGroup(int $userId, string $group)
    {
        $sql = "INSERT INTO auth_groups_users (user_id, user_group) VALUES (:userId, :group)";

        return $this->insert($sql, ['userId' => $userId, 'group' => $group]);
    }

    /**
     * Insert a login attempt for a user
     * 
     * @param int|null $userId
     * @param string $email
     * @param int $success 0 = failed, 1 = success
     * @return int|false
     */
    public function insertNewLoginAttempt(int|null $userId, string $email, int $success)
    {
        $sql = "INSERT INTO auth_logins_users (user_id, identifier, success) VALUES (:userId, :identifier, :success)";

        return $this->insert($sql, ['userId' => $userId, 'identifier' => $email, 'success' => $success]);
    }

    /**
     * Delete a user from the users table
     * 
     * @param int $userId
     * @return int|false
     */
    public function deleteUser(int $userId)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :userId";

        return $this->insert($sql, ['userId' => $userId]);
    }

    /**
     * Delete all identity records for a user
     * 
     * @param int $userId
     * @return int|false
     */
    public function deleteUserIdentity(int $userId)
    {
        $sql = "DELETE FROM auth_identities_users WHERE user_id = :userId";

        return $this->insert($sql, ['userId' => $userId]);
    }

    /**
     * Delete a user's group(s)
     * 
     * @param int $userId
     * @param string|null $group Optional: delete specific group
     * @return int|false
     */
    public function deleteUserGroup(int $userId, string|null $group = null)
    {
        $sql = "DELETE FROM auth_groups_users WHERE user_id = :userId";

        $binds = ['userId' => $userId];
        if($group){
            $sql .= " AND group = :group";
            $binds['group'] = $group;
        }

        return $this->insert($sql, $binds);
    }

    // TODO: Future improvements for password resets, profile updates, etc.
}
