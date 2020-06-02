<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Users
 *
 * @ORM\Table(name="users")
 * @ORM\Entity
 */
class User implements \JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=150, nullable=false)
     */
    private $name;

    /**
     * @var int|null
     *
     * @ORM\Column(name="documento", type="integer", nullable=true)
     */
    private $documento;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var int
     *
     * @ORM\Column(name="celular", type="integer", nullable=false)
     */
    private $celular;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     */
    private $password;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

     /**
     * @ORM\OneToMany(targetEntity="App\Entity\Whallet", mappedBy="user")
     */
    private $whallets;

    public function __construct(){
        $this->whallets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDocumento(): ?int
    {
        return $this->documento;
    }

    public function setDocumento(?int $documento): self
    {
        $this->documento = $documento;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCelular(): ?int
    {
        return $this->celular;
    }

    public function setCelular(int $celular): self
    {
        $this->celular = $celular;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|Whallet[]
     */
    public function getWhallets(): Collection{
        return $this->whallets;
    }

     public function jsonSerialize(): array
    {
        return [
            'id'=> $this->id,
            'name'=> $this->name,
            'email'=>$this->email,
            'documento'=>$this->documento,
            'celular'=>$this->celular,
        ];
    }

}
