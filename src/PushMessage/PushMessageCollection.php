<?php

namespace Dru1x\ExpoPush\PushMessage;

use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\PushToken\PushTokenCollection;
use Dru1x\ExpoPush\Support\Collection;
use Dru1x\ExpoPush\Support\CollectionMethods;
use ValueError;

/**
 * A collection of PushMessage objects
 */
final class PushMessageCollection implements Collection
{
    /** @use CollectionMethods<int, PushMessage> */
    use CollectionMethods;

    public function __construct(PushMessage ...$pushMessages)
    {
        $this->items = $pushMessages;
    }

    /**
     * Count the number of notifications that would be sent by this set of PushMessages
     *
     * PushMessage objects can each have multiple recipients, and each of these would result in a single notification.
     * This method takes this into account and counts the total number of resultant notifications.
     *
     * @return int
     */
    public function notificationCount(): int
    {
        return array_reduce($this->items, fn(int $count, PushMessage $message) => $count + $message->tokenCount(), 0);
    }

    /**
     * Break this collection into smaller chunks where the total notification count doesn't exceed the given size
     *
     * PushMessage objects can each have multiple recipients, and each of these would result in a single notification.
     * This method takes this into account and splits the messages into multiple chunks where the total number of
     * resultant notifications does not exceed the given size.
     *
     * @param int $size
     *
     * @return array<array-key, PushMessageCollection>
     */
    public function chunkByNotifications(int $size): array
    {
        if ($size < 1) {
            throw new ValueError('Size must be greater than 0');
        }

        $currentChunk = new self();
        $allChunks    = [$currentChunk];

        foreach ($this->items as $pushMessage) {

            // Calculate how much notification space is left in the current chunk
            $currentChunkCapacity = $this->calculateChunkCapacity($size, $currentChunk, $allChunks);

            // Get the number of notifications the current message will send
            $notificationCount = $pushMessage->tokenCount();

            // If the current message will fit in the current chunk, add it now and move on to the next one
            if ($currentChunkCapacity >= $notificationCount) {
                $currentChunk->add($pushMessage);
                continue;
            }

            // Get all the recipient tokens from the message
            $recipientTokens = $pushMessage->allTokens();

            // Iterate over the tokens, adding them to a split copy of the message
            while($recipientTokens->isNotEmpty()) {

                // Calculate how much notification space is left in the current chunk
                $currentChunkCapacity = $this->calculateChunkCapacity($size, $currentChunk, $allChunks);

                // Take as many tokens as will fit in the chunk, add them to a message copy, and add it to the chunk
                $currentChunk->add(
                    $pushMessage->copy(to: $recipientTokens->shift($currentChunkCapacity))
                );
            }
        }

        return $allChunks;
    }

    /**
     * Get an ordered collection of all push tokens used by the push messages in this collection
     *
     * @return PushTokenCollection
     */
    public function getTokens(): PushTokenCollection
    {
        $extractPushTokens = fn(array $carry, PushMessage $message) => array_merge($carry,
            $message->to instanceof PushToken ? [$message->to] : $message->to->all()
        );

        return new PushTokenCollection(
            ...array_reduce($this->items, $extractPushTokens, [])
        );
    }

    // Internals ----

    /**
     * Calculate the amount of space remaining in a chunk of PushMessages.
     *
     * This uses notification counts rather than message counts, and makes a new chunk
     * when the current one is full.
     */
    protected function calculateChunkCapacity(int $size, self &$currentChunk, array &$allChunks): int
    {
        // Calculate how much notification space is left in the current chunk
        $currentChunkCapacity = $size - $currentChunk->notificationCount();

        // If the chunk is already full, start a new one
        if ($currentChunkCapacity <= 0) {
            $currentChunk = new self();
            $allChunks[] = $currentChunk;
            $currentChunkCapacity = $size;
        }

        // Return the remaining number of notifications allowed in the current chunk
        return $currentChunkCapacity;
    }
}