<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Tests\Functional\Fixtures\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

if (PHP_VERSION_ID >= 80000) {
    // PHP 8+ - use attributes (required for Symfony 7)
    #[ORM\Entity]
    class PersistedEntity
    {
        #[ORM\Id]
        #[ORM\GeneratedValue(strategy: "AUTO")]
        #[ORM\Column(type: "integer")]
        private $id;

        #[ORM\Column(type: "string")]
        private $field;

        #[ORM\ManyToOne(targetEntity: PersistedEntity::class)]
        private $parent;

        #[ORM\OneToMany(targetEntity: PersistedEntity::class, cascade: ["all"], mappedBy: "parent")]
        private $children;

        public function __construct()
        {
            $this->children = new ArrayCollection();
        }

        public function getField(): string
        {
            return $this->field;
        }

        public function setField(string $field): self
        {
            $this->field = $field;
            return $this;
        }

        public function getId(): ?int
        {
            return $this->id;
        }

        public function getParent(): ?self
        {
            return $this->parent;
        }

        public function setParent(?self $parent): self
        {
            $this->parent = $parent;
            return $this;
        }

        public function getChildren(): Collection
        {
            return $this->children;
        }

        public function addChild(self $child): self
        {
            $this->children[] = $child;
            $child->setParent($this);
            return $this;
        }
    }
} else {
    // PHP 7 - use annotations
    /**
     * @ORM\Entity
     */
    class PersistedEntity
    {
        /**
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="AUTO")
         * @ORM\Column(type="integer")
         * @var int|null
         */
        private $id;

        /**
         * @var string|null
         * @ORM\Column(type="string")
         */
        private $field;

        /**
         * @var PersistedEntity|null
         * @ORM\ManyToOne(targetEntity="PersistedEntity")
         */
        private $parent;

        /**
         * @var PersistedEntity[]|Collection
         * @ORM\OneToMany(targetEntity="PersistedEntity", cascade={"all"}, mappedBy="parent")
         */
        private $children;

        public function __construct()
        {
            $this->children = new ArrayCollection();
        }

        public function getField(): string
        {
            return $this->field;
        }

        public function setField(string $field): self
        {
            $this->field = $field;
            return $this;
        }

        public function getId(): ?int
        {
            return $this->id;
        }

        public function getParent(): ?self
        {
            return $this->parent;
        }

        public function setParent(?self $parent): self
        {
            $this->parent = $parent;
            return $this;
        }

        public function getChildren(): Collection
        {
            return $this->children;
        }

        public function addChild(self $child): self
        {
            $this->children[] = $child;
            $child->setParent($this);
            return $this;
        }
    }
}
