<?php

require_once "./Core/Controller.php";

class FriendsController extends Controller
{
    public const ROUTES = [
        'friends.delete' => ['user_only' => true, 'params' => ['friendId']],
    ];

    function create(int $senderId, int $receiverId): int
    {
        $this->db->query("DELETE FROM friends_requests WHERE sender = {$receiverId} AND receiver = {$senderId}");
        return $this->db->insert('friends', [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
        ]);
    }

    function destroy(int $senderId, int $receiverId): void
    {
        $this->db->query("DELETE FROM friends WHERE sender_id = {$senderId} AND receiver_id = {$receiverId}");
    }

    function delete(array $request): array
    {
        $userId = $_SESSION['userId'];
        $friendId = $request['friendId'];

        if (false == $this->friendshipExists($userId, $friendId)) {
            return ['result' => 'error', 'message' => 'Friendship does not exist.'];
        }

        $this->destroy($userId, $friendId);
        $this->destroy($friendId, $userId);

        return ['result' => 'success', 'message' => 'Friendship deleted.'];
    }

    function friendshipExists(int $senderId, int $receiverId): bool
    {
        $existingFriendship = $this->db->selectOne("
            SELECT receiver_id
            FROM friends
            WHERE (sender_id = {$senderId}) AND receiver_id = {$receiverId})
               OR (sender_id = {$receiverId}) AND receiver_id = {$senderId})
        ");

        return false == empty($existingFriendship);
    }
}
