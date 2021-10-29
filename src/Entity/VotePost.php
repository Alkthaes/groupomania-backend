<?php

namespace App\Entity;

use App\Repository\VotePostRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VotePostRepository::class)
 */
class VotePost
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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="votePosts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Post::class, inversedBy="votePosts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $post;

    /**
     * @ORM\Column(type="boolean")
     */
    private $downvote;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

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
}
