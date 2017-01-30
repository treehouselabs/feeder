## 1.1.1

* Supressed FTP login warnings (#14)


## 1.1.0

* Added JsonLines support. See http://jsonlines.org for information about the format.


## 1.0.0

This is the first stable release of Feeder and has diverged a bit from the
original [`fm/feeder`][1] library. With the 1.0 release the library follows
semantic versioning, which means no breaking changes until version 2.

[1]: https://github.com/financialmedia/feeder

### BC breaks

* Dependencies
  * Feeder now uses Guzzle version 6 instead of the deprecated version 3. If you
    are extending functionality from the library that addresses Guzzle classes,
    you need to update those imports and possibly method calls.
* Renamed classes, interfaces, and methods
  * Suffixed `Transport` and `Resource` with `Interface`
  * Renamed `download` to `fetch` in `TransportInterface`. Also the `doDownload`
    and `needsDownload` methods were renamed to `doFetch` and `isFresh`
    respectively, with the latter having the logic inversed compared to before.
  * Some `FeedEvents` constants have been renamed to reflect the naming changes.
    Also the constant values have changed, so if you're using those instead of
    the constants, you need to update those references.
  * The `DownloadProgressEvent` was renamed to `FetchProgressEvent`, likewise
    the `getBytesDownloaded` and `getTotal` methods have been renamed respectively
    to `getBytesFetched` and `getBytesTotal`. The `getBytesWritten` method was
    dropped from this event.
  * `LocalizedStringToDateTimeTransformer` was renamed to `StringToDateTimeTransformer`.
* Removed classes, interfaces, and methods
  * Removed `useInfo` from HttpTransport
  * Removed deprecated `RemoveUnitSeparatorsTransformer` in favor of `RemoveControlCharactersTransformer`
  * Removed `getSource`, `getSize`, and `getDestination` from TransportEvent
* API/functionality changes
  * **`ResourceCollection`**
    The `ResourceCollection` now extends `SplStack` instead of `SplQueue`. That
    means that the `enqueue` and `dequeue` methods are gone. Also the custom
    `enqueueAll` method was renamed to `pushAll` to maintain consistency with
    the extended class.
  * **`LocalizedStringToDateTimeTransformer`**
    The `LocalizedStringToDateTimeTransformer` was renamed to
    `StringToDateTimeTransformer` and has a different constructor signature:

    ```diff
    -public function __construct($locale = null, $inputTimezone = null, $outputTimezone = null, $dateFormat = null, $timeFormat = null, $calendar = \IntlDateFormatter::GREGORIAN, $pattern = null)
    +public function __construct($format = 'Y-m-d H:i:s', $inputTimezone = null, $outputTimezone = null, $resetFields = true)
    ```

    This change is because the transformer no longer uses the `\DateFormatter`
    class, but just the `\DateTime::createFromFormat()` method, for which you
    can provide any format you want.
  * **`LocalizedStringToNumberTransformer`**
    The `LocalizedStringToNumberTransformer` has a different constructor signature:

    ```diff
    -public function __construct($type = \NumberFormatter::TYPE_DOUBLE, $precision = null, $grouping = null, $roundingMode = null, $locale = null)
    +public function __construct($locale = null, $precision = null, $grouping = null, $roundingMode = null)
    ```

    This is because the type can be inferred by the `$precision` argument, and
    `$locale` is the main argument here (hence the transformer's name) so it
    makes sense to specify it first.
  * **`ExpandAttributesTransformer`**
    The `ExpandAttributesTransformer` now expands attributes on the same level,
    rather than the level above it.

    Example:

    ```php
    $item = new ParameterBag([
      'node' => [
        '@id' => 1234,
      ]
    ]);

    print_r((new ExpandAttributesTransformer())->transform($item));
    ```

    Before:

    ```
    Array
    (
        [id] => 1234
        [node] => Array
            (
                [@id] => 1234
            )

    )
    ```

    After:

    ```
    Array
    (
        [node] => Array
            (
                [id] => 1234
                [@id] => 1234
            )

    )
    ```
