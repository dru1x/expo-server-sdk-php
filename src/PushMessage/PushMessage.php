<?php

namespace Dru1x\ExpoPush\PushMessage;

use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\PushToken\PushTokenCollection;
use Dru1x\ExpoPush\Support\ConvertsFromJson;
use Dru1x\ExpoPush\Support\ConvertsToJson;
use InvalidArgumentException;
use JsonSerializable;

final readonly class PushMessage implements JsonSerializable
{
    use ConvertsFromJson, ConvertsToJson;

    public function __construct(
        public PushTokenCollection|PushToken $to,
        public ?string                       $title = null,
        public ?string                       $subtitle = null,
        public ?string                       $body = null,
        public ?int                          $ttl = null,
        public array|object|null             $data = null,
        public ?int                          $expiration = null,
        public ?Priority                     $priority = null,
        public ?string                       $sound = null,
        public ?int                          $badge = null,
        public ?InterruptionLevel            $interruptionLevel = null,
        public ?string                       $channelId = null,
        public ?string                       $icon = null,
        public ?RichContent                  $richContent = null,
        public ?string                       $categoryId = null,
        public ?string                       $collapseId = null,
        public ?string                       $tag = null,
        public ?bool                         $mutableContent = null,
        public ?bool                         $_contentAvailable = null,
    ) {}

    // Helpers ----

    /**
     * Make a copy of this PushMessage, optionally overriding the recipients
     *
     * @param PushTokenCollection|PushToken|null $to
     *
     * @return self
     */
    public function copy(PushTokenCollection|PushToken|null $to = null): self
    {
        return new PushMessage(
            to: $to ?? clone $this->to,
            title: $this->title,
            subtitle: $this->subtitle,
            body: $this->body,
            ttl: $this->ttl,
            data: $this->data,
            expiration: $this->expiration,
            priority: $this->priority,
            sound: $this->sound,
            badge: $this->badge,
            interruptionLevel: $this->interruptionLevel,
            channelId: $this->channelId,
            icon: $this->icon,
            richContent: $this->richContent? clone $this->richContent : null,
            categoryId: $this->categoryId,
            collapseId: $this->collapseId,
            tag: $this->tag,
            mutableContent: $this->mutableContent,
            _contentAvailable: $this->_contentAvailable,
        );
    }

    // Internals ----

    /** @inheritDoc */
    public function jsonSerialize(): array
    {
        $allFields = get_object_vars($this);

        $allFields['data'] = isset($allFields['data'])? (object) $allFields['data'] : null;

        return array_filter($allFields, fn(mixed $value) => !is_null($value));
    }

    public static function fromArray(array $data): self
    {
        if(!isset($data['to']) || !is_array($data['to']) && !is_string($data['to'])) {
            throw new InvalidArgumentException('A push message requires at least one recipient token');
        }

        $data['to'] = is_array($data['to'])
            ? PushTokenCollection::fromArray($data['to'])
            : PushToken::fromString($data['to']);

        if(isset($data['priority'])) {
            $data['priority'] = Priority::from($data['priority']);
        }

        if(isset($data['interruptionLevel'])) {
            $data['interruptionLevel'] = InterruptionLevel::from($data['interruptionLevel']);
        }

        if(isset($data['richContent'])) {
            $data['richContent'] = RichContent::fromArray($data['richContent']);
        }

        return new self(...$data);
    }
}
