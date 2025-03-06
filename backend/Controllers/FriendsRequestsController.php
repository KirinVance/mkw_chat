<?php

require_once "./Core/Controller.php";
require_once "./Controllers/FriendsController.php";

class FriendsRequestsController extends Controller
{
    public const ROUTES = [
        "friends_requests.send"   => ['user_only' => true, 'params' => ['receiverId']],
        "friends_requests.cancel" => ['user_only' => true, 'params' => ['receiverId']],
        "friends_requests.accept" => ['user_only' => true, 'params' => ['senderId']],
        "friends_requests.reject" => ['user_only' => true, 'params' => ['senderId']],
    ];

    function create(int $senderId, int $receiverId): void
    {
        $this->db->insert('friends_requests', [
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
        ]);
    }

    function destroy(int $senderId, int $receiverId): void
    {
        $this->db->query("DELETE FROM friends_requests WHERE sender = {$senderId} AND receiver = {$receiverId}");
    }

    function exists(int $senderId, int $receiverId): bool
    {
        return false == empty($this->db->selectOne("SELECT receiver_id FROM friends_requests WHERE sender_id = {$senderId} AND receiver_id = {$receiverId}"));
    }

    function send(array $request): void
    {
        $senderId = $_SESSION['userId'];
        $receiverId = $request['receiverId'];

        $friendsController = new FriendsController($this->db);
        $friendshipAlreadyExists = $friendsController->friendshipExists($senderId, $receiverId);
        if ($friendshipAlreadyExists) {
            $this->jsonResponse(['result' => 'error', 'message' => 'Friendship already exists.']);
        }

        if ($this->exists($senderId, $receiverId)) {
            $this->jsonResponse(['result' => 'error', 'message' => 'Request already exists.']);
        }

        if ($this->exists($receiverId, $senderId)) {
            $this->destroy($receiverId, $senderId);
            (new FriendsController($this->db))->create($senderId, $receiverId);

            $this->jsonResponse(['result' => 'success', 'message' => 'Request sent and accepted.']);
        }

        $this->create($senderId, $receiverId);

        $this->jsonResponse(['result' => 'success', 'message' => 'Request sent.']);
    }

    function cancel(array $request): void
    {
        $senderId = $_SESSION['userId'];
        $receiverId = $request['receiverId'];

        if (false == $this->exists($senderId, $receiverId)) {
            $this->jsonResponse(['result' => 'error', 'message' => 'Request does not exist.']);
        }

        $this->destroy($senderId, $receiverId);
        $this->jsonResponse(['result' => 'success', 'message' => 'Request deleted.']);
    }

    function accept(array $request): void
    {
        $receiverId = $_SESSION['userId'];
        $senderId = $request['senderId'];

        if (false == $this->exists($senderId, $receiverId)) {
            $this->jsonResponse(['result' => 'error', 'message' => 'Request does not exist.']);
        }

        $friendsController = new FriendsController($this->db);
        if ($friendsController->friendshipExists($senderId, $receiverId)) {
            $this->jsonResponse(['result' => 'error', 'message' => 'Friendship already exists.']);
        }

        $this->destroy($senderId, $receiverId);
        $this->destroy($receiverId, $senderId);
        $friendsController->create($senderId, $receiverId);

        $this->jsonResponse(['result' => 'success', 'message' => 'Request accepted.']);
    }

    function reject(array $request): void
    {
        $receiverId = $_SESSION['userId'];
        $senderId = $request['senderId'];

        if (false == $this->exists($senderId, $receiverId)) {
            $this->jsonResponse(['result' => 'error', 'message' => 'Request does not exist.']);
        }

        $this->destroy($senderId, $receiverId);

        $this->jsonResponse(['result' => 'success', 'message' => 'Request rejected.']);
    }
}
