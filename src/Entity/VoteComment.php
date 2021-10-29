<?php

namespace App\Entity;

use App\Repository\VoteCommentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VoteCommentRepository::class)
 */
class VoteComment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $upvote;

    /**
     * @ORM\Column(type="boolean")
     */
    private $downvote;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="voteComments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Comment::class, inversedBy="voteComments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $comment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpvote(): ?bool
    {
        return $this->upvote;
    }

    public function setUpvote(bool $upvote): self
    {
        $this->upvote = $upvote;

        return $this;
    }

    public function getDownvote(): ?bool
    {
        return $this->downvote;
    }

    public function setDownvote(bool $downvote): self
    {
        $this->downvote = $downvote;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
