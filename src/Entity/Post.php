<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"post:read", "comment:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"post:read", "comment:read"})
     */
    private $titre;

    /**
     * @ORM\Column(type="text")
     * @Groups("post:read")
     */
    private $image;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"post:read", "comment:read"})
     */
    private $creation_date;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("post:read")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="post", orphanRemoval=true)
     * @Groups("post:read")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=VotePost::class, mappedBy="post", orphanRemoval=true)
     * @Groups("post:read")
     */
    private $votePosts;


    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->votePosts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getImage(): ?string
    {
        //conversion de l'image en base64 avant de l'envoyer vers le front
        $imgPath = $this->image;
        $imgExtension = explode('.', $imgPath);
        $img = file_get_contents($imgPath);
        $data = base64_encode($img);

        return 'data:image/' . $imgExtension[1] . ';base64,' . $data;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creation_date;
    }

    public function setCreationDate(\DateTimeInterface $creation_date): self
    {
        $this->creation_date = $creation_date;

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

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|VotePost[]
     */
    public function getVotePosts(): Collection
    {
        return $this->votePosts;
    }

    public function addVotePost(VotePost $votePost): self
    {
        if (!$this->votePosts->contains($votePost)) {
            $this->votePosts[] = $votePost;
            $votePost->setPost($this);
        }

        return $this;
    }

    public function removeVotePost(VotePost $votePost): self
    {
        if ($this->votePosts->removeElement($votePost)) {
            // set the owning side to null (unless already changed)
            if ($votePost->getPost() === $this) {
                $votePost->setPost(null);
            }
        }

        return $this;
    }
}
