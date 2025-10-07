<?php

namespace Models;

use Config\BaseModel;

/**
 * Class TicketModel
 * 
 * Handles all database operations related to tickets, 
 * including fetching public tickets, organizer-specific tickets, 
 * creating, updating, and soft-deleting tickets.
 */
class TicketModel extends BaseModel
{
    /**
     * Initialize table and primary key
     */
    public function __construct()
    {
        $this->table = "tickets_with_buyers";
        $this->primaryKey = "id";
    }

    /**
     * Fetch all public tickets within active date range
     * 
     * @param int $start Offset for pagination
     * @param int $display Number of items to display
     * @param string|null $order Column to order by (optional, currently unused)
     * @param string|null $dir Sort direction (ASC/DESC) (optional, currently unused)
     * @return array
     */
    public function getAllPublicTickets($search){
        $sql = 
            "SELECT 
                t.*,
                u.name
            FROM {$this->table} t
            JOIN users u ON u.id = t.creator_id
            WHERE visibility = 1 AND deleted = 0 and now() BETWEEN starts_at AND ends_at
            AND (t.title LIKE :search OR u.name LIKE :search)
            ORDER BY ends_at ASC";

        return $this->select($sql, ["search" => "%$search%"]);
    }

    /**
     * Fetch tickets created by a specific organizer
     * 
     * @param int $orgId Organizer user ID
     * @param int $deleted Include deleted tickets (0 = no, 1 = yes)
     * @param int $start Pagination offset
     * @param int $display Pagination limit
     * @param string|null $order Column to order by (optional)
     * @param string|null $dir Direction (ASC/DESC) (optional)
     * @return array
     */
    public function getTicketsByOrganizer($orgId, $deleted, $search){
        $sql = 
            "SELECT *
            FROM {$this->table}
            WHERE deleted = :del AND creator_id = :orgId
            AND title LIKE :search 
            ORDER BY ends_at ASC";        
        
        return $this->select($sql, ["del" => $deleted, "orgId" => $orgId, "search" => "%$search%"]);
    }

    /**
     * Fetch a ticket if the given user is the creator
     * 
     * @param int $creatorId
     * @param int $tId Ticket ID
     * @param int $deleted
     * @return array
     */
    public function getTicketIfOwner($creatorId, $tId, $deleted){
        $sql = 
            "SELECT *
             FROM {$this->table}
             WHERE id = :tId AND deleted = :deleted AND creator_id = :creatorId
             ORDER BY ends_at ASC";

        return $this->select($sql, ["creatorId" => $creatorId, "tId" => $tId, "deleted" => $deleted]);
    }

    /**
     * Fetch a ticket by its ID
     * 
     * @param int $ticketId
     * @param int|null $public If set, filter by visibility
     * @return array
     */
    public function getTicketById($ticketId, $public = null){
        $sql = "SELECT * FROM {$this->table} WHERE id = :ticketId";
        $binds = ["ticketId" => $ticketId];

        if(!is_null($public)){
            $sql .= " AND visibility = :public";
            $binds['public'] = $public;
        }

        return $this->select($sql, $binds);
    }

    /**
     * Insert a new ticket
     * 
     * @param string $title
     * @param int $creatorId
     * @param int $visibility 0 = hidden, 1 = public
     * @param int $max Maximum quantity
     * @param string $starts Start datetime
     * @param string $ends End datetime
     * @param string $image Image filename
     * @param float $price Ticket price
     * @return int|false Inserted ticket ID or false on failure
     */
    public function insertNewTicket($title, $creatorId, $visibility, $max, $starts, $ends, $image, $price){
        $sql = "INSERT INTO tickets(creator_id, title, starts_at, ends_at, max_quantity, price, visibility, image)
                VALUES (:creatorId, :title, :starts, :ends, :max, :price, :visibility, :image)";

        return $this->insert($sql, [
            'creatorId' => $creatorId, 
            'title' => $title, 
            'starts' => $starts, 
            'ends' => $ends, 
            'max' => $max, 
            'price' => $price, 
            'visibility' => $visibility, 
            'image' => $image
        ]);
    }

    /**
     * Update an existing ticket by its ID
     * Only updates image if provided
     * 
     * @param int $id
     * @param string $title
     * @param int $creatorId
     * @param int $visibility
     * @param int $max
     * @param string $starts
     * @param string $ends
     * @param string $image
     * @param float $price
     * @return int|false
     */
    public function updateTicketById($id, $title, $creatorId, $visibility, $max, $starts, $ends, $image, $price){
        $sql = 
            "UPDATE tickets SET 
                    title = :title,
                    updated_at = NOW(),
                    starts_at = :starts,
                    ends_at = :ends,
                    visibility = :visibility,
                    max_quantity = :max,
                    price = :price";

        $binds = [
            'ticketId' => $id,
            'creatorId' => $creatorId,
            'title' => $title,
            'starts' => $starts,
            'ends' => $ends,
            'max' => $max,
            'price' => $price,
            'visibility' => $visibility,
        ];

        // Only include image if provided
        if ($image !== null && $image !== "") {
            $sql .= ", image = :image";
            $binds['image'] = $image;
        }

        $sql .= " WHERE id = :ticketId AND creator_id = :creatorId";

        return $this->update($sql, $binds);
    }

    /**
     * Soft delete a ticket (mark as deleted without removing from DB)
     * 
     * @param int $id Ticket ID
     * @param int $creatorId User who owns the ticket
     * @return int|false
     */
    public function softDeleteTicket($id, $creatorId){
        $sql = "UPDATE tickets SET deleted = 1, deleted_at = NOW() WHERE id = :ticketId AND creator_id = :creatorId AND deleted = 0";
        
        return $this->update($sql, ['ticketId' => $id, 'creatorId' => $creatorId]);
    }

    /**
     * Restore a ticket (set deleted flag to 0 without inserting new to DB)
     * 
     * @param int $id Ticket ID
     * @param int $creatorId User who owns the ticket
     * @return int|false
     */
    public function restoreTicket($id, $creatorId){
        $sql = "UPDATE tickets SET deleted = 0, deleted_at = null WHERE id = :ticketId AND creator_id = :creatorId AND deleted = 1";
        
        return $this->update($sql, ['ticketId' => $id, 'creatorId' => $creatorId]);
    }
}
