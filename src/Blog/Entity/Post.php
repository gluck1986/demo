<?php

declare(strict_types=1);

namespace App\Blog\Entity;

use App\User\User;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Cycle\Annotated\Annotation\Relation\HasMany;
use Cycle\Annotated\Annotation\Relation\ManyToMany;
use Cycle\Annotated\Annotation\Table\Index;
use Cycle\ORM\Collection\Pivoted\PivotedCollection;
use Cycle\ORM\Entity\Behavior;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Yiisoft\Security\Random;

#[Entity(
    repository: \App\Blog\Post\PostRepository::class,
    scope: \App\Blog\Post\Scope\PublicScope::class
)]
#[Index(columns: ['public', 'published_at'])]
#[Behavior\CreatedAt(field: 'created_at', column: 'created_at')]
#[Behavior\UpdatedAt(field: 'updated_at', column: 'updated_at')]
#[Behavior\SoftDelete(field: 'deleted_at', column: 'deleted_at')]
class Post
{
    #[Column(type: 'primary')]
    private ?int $id = null;

    #[Column(type: 'string(128)')]
    private string $slug;

    #[Column(type: 'string(191)', default: '')]
    private string $title = '';

    #[Column(type: 'bool', default: 'false', typecast: 'bool')]
    private bool $public = false;

    #[Column(type: 'text')]
    private string $content = '';

    #[Column(type: 'datetime')]
    private DateTimeImmutable $created_at;

    #[Column(type: 'datetime')]
    private DateTimeImmutable $updated_at;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeImmutable $published_at = null;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeImmutable $deleted_at = null;

    #[BelongsTo(target: User::class, nullable: false)]
    private ?User $user = null;
    private ?int $user_id = null;

    /**
     * @var PivotedCollection<array-key, Tag, PostTag>
     */
    #[ManyToMany(target: Tag::class, though: PostTag::class, fkAction: 'CASCADE')]
    private PivotedCollection $tags;

    /**
     * @var ArrayCollection<array-key, Comment>
     */
    #[HasMany(target: Comment::class)]
    private ArrayCollection $comments;

    public function __construct(string $title = '', string $content = '')
    {
        $this->title = $title;
        $this->content = $content;
        $this->created_at = new DateTimeImmutable();
        $this->updated_at = new DateTimeImmutable();
        $this->tags = new PivotedCollection();
        $this->comments = new ArrayCollection();
        $this->resetSlug();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function resetSlug(): void
    {
        $this->slug = Random::string(128);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deleted_at;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->published_at;
    }

    public function setPublishedAt(?DateTimeImmutable $published_at): void
    {
        $this->published_at = $published_at;
    }

    /**
     * @return Comment[]
     */
    public function getComments(): array
    {
        return $this->comments->toArray();
    }

    public function addComment(Comment $post): void
    {
        $this->comments->add($post);
    }

    /**
     * @return Tag[]
     */
    public function getTags(): array
    {
        return $this->tags->toArray();
    }

    public function addTag(Tag $post): void
    {
        $this->tags->add($post);
    }

    public function resetTags(): void
    {
        $this->tags->clear();
    }

    public function isNewRecord(): bool
    {
        return $this->getId() === null;
    }
}
