<?php

namespace TreeHouse\Feeder;

final class FeedEvents
{
    /**
     * When cached version is used.
     */
    const FETCH_CACHED = 'feeder.transport.fetch.cached';

    /**
     * When starting to fetch.
     */
    const PRE_FETCH = 'feeder.transport.fetch.pre';

    /**
     * After fetch is complete.
     */
    const POST_FETCH = 'feeder.transport.fetch.post';

    /**
     * When fetch is in progress.
     */
    const FETCH_PROGRESS = 'feeder.transport.fetch.progress';

    /**
     * When starting to import a resource.
     */
    const RESOURCE_START = 'feeder.feed.resource.start';

    /**
     * When ending a resource.
     */
    const RESOURCE_END = 'feeder.feed.resource.end';

    /**
     * Before serializing an item.
     */
    const RESOURCE_PRE_SERIALIZE = 'feeder.feed.resource.serialize.pre';

    /**
     * After serializing an item.
     */
    const RESOURCE_POST_SERIALIZE = 'feeder.feed.resource.serialize.post';

    /**
     * Before modifying an item.
     */
    const ITEM_PRE_MODIFICATION = 'feeder.item.modification.pre';

    /**
     * After modifying an item.
     */
    const ITEM_POST_MODIFICATION = 'feeder.item.modification.post';

    /**
     * When a modifier fails. Note that this does not have to mean the entire item fails.
     * This is determined by the continue property of the modifier and/or this event.
     */
    const ITEM_MODIFICATION_FAILED = 'feeder.item.modification.failed';

    /**
     * When an item is filtered during modification.
     */
    const ITEM_FILTERED = 'feeder.item.filtered';

    /**
     * When an item is found invalid during modification.
     */
    const ITEM_INVALID = 'feeder.item.invalid';

    /**
     * When an item is failed during modification.
     */
    const ITEM_FAILED = 'feeder.item.failed';
}
