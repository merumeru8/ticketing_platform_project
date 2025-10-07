<?php

namespace Models;

use Config\BaseModel;

class BuyerModel extends BaseModel
{
    /**
     * Constructor sets table and primary key
     */
    public function __construct()
    {
        $this->table = "buyers";
        $this->primaryKey = "id";
    }

    /**
     * Insert new buyer entries for a single ticket
     *
     * @param int $userId   ID of the user buying tickets
     * @param int $ticketId ID of the ticket
     * @param int $num      Number of tickets to insert
     * @return mixed        Insert result (ID or false)
     */
    public function insertNewBuyerForOneTicket($userId, $ticketId, $num)
    {
        $sql = "INSERT INTO {$this->table} (ticket_id, user_id) VALUES ";

        $binds = [];
        $placeholder = [];

        // Repeat placeholders for the number of tickets
        for ($i = 0; $i < $num; $i++) {
            $placeholder[] = "(?, ?)";
            $binds[] = $ticketId;
            $binds[] = $userId;
        }

        // Combine all placeholders into SQL
        $sql .= implode(", ", $placeholder);

        // Execute insert with positional bindings
        return $this->insert($sql, $binds);
    }

    /**
     * Insert new buyer entries for multiple tickets
     *
     * @param int   $userId  ID of the user buying tickets
     * @param array $tickets Associative array of ticket IDs and quantities 
     *                       e.g., [ticketId => num, ticketId2 => num2, ...]
     * @return mixed         Insert result (ID or false)
     */
    public function insertNewBuyerForMultiTicket($userId, $tickets)
    {
        $sql = "INSERT INTO {$this->table} (ticket_id, user_id) VALUES ";

        $binds = [];
        $placeholder = [];

        // Loop through tickets and quantities
        foreach ($tickets as $tId => $num) {
            for ($i = 0; $i < $num; $i++) {
                $placeholder[] = "(?, ?)";
                $binds[] = $tId;
                $binds[] = $userId;
            }
        }

        // Combine all placeholders into SQL
        $sql .= implode(", ", $placeholder);

        // Execute insert with positional bindings
        return $this->insert($sql, $binds);
    }

    /**
     * Archive all buyers associated with a specific ticket
     *
     * @param int    $ticketId The ID of the ticket whose buyers should be archived.
     * @param string $reason   The reason for archiving the buyers.
     *
     * @return bool  Returns the result of the update operation.
     */
    public function archiveTicketBuyers($ticketId, $reason)
    {
        $sql = "UPDATE {$this->table} SET archived = 1, archived_at = NOW(), reason_archive = :reason WHERE ticket_id = :ticketId AND archived = 0";

        return $this->update($sql, ['ticketId' => $ticketId, "reason" => $reason]);
    }

    /**
     * Unarchive all buyers associated with a specific ticket
     *
     * @param int    $ticketId The ID of the ticket whose buyers should be archived.
     * @param string $reason   The reason for archiving the buyers.
     *
     * @return bool  Returns the result of the update operation.
     */
    public function unarchiveTicketBuyers($ticketId)
    {
        $sql = "UPDATE {$this->table} SET archived = 0, archived_at = null, reason_archive = null WHERE ticket_id = :ticketId AND archived = 1";

        return $this->update($sql, ['ticketId' => $ticketId]);
    }

    /**
     * Archive a specific buyer by their user ID.
     *
     * @param int    $userId The ID of the user to archive.
     * @param string $reason The reason for archiving the buyer.
     *
     * @return bool  Returns the result of the update operation.
     */
    public function archiveUserBuyer($userId, $reason)
    {
        $sql = "UPDATE {$this->table} SET archived = 1, archived_at = NOW(), reason_archive = :reason WHERE user_id = :userId AND archived = 0";

        return $this->update($sql, ['userId' => $userId, "reason" => $reason]);
    }
}
