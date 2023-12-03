<?php

namespace App\Message;

final class Notification
{
    private $email;
    private $content;
    private $userId;
    private $collegeId;

    public function __construct(string $email, string $content, int $userId, int $collegeId)
    {
        $this->email = $email;
        $this->content = $content;
        $this->userId = $userId;
        $this->collegeId = $collegeId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getCollegeId(): int
    {
        return $this->collegeId;
    }
}
