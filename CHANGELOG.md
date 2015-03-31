## Version 1.0

This is the first stable release of Feeder and has diverged a bit from the original [`fm/feeder`][1] library. With the 1.0 release the library follows semantic versioning, which means no breaking changes until version 2.

If you were using the alpha version, you have to check/fix the following BC breaks:

[1]: https://github.com/financialmedia/feeder

### Dependencies

Feeder now uses Guzzle version 5 instead of the deprecated version 3.


### Renamed classes, interfaces, and methods

* Suffixed `Transport` and `Resource` with `Interface`
* Renamed `download` to `fetch` in `TransportInterface`. Also the `doDownload` and `needsDownload` methods were renamed
  to `doFetch` and `isFresh` respectively, with the latter having the logic inversed compared to before.
* Some `FeedEvents` constants have been renamed to reflect the naming changes. Also the constant values have changed, so
  if you're using those instead of the constants, you need to update those references.
* The `DownloadProgressEvent` was renamed to `FetchProgressEvent`, likewise the `getBytesDownloaded` and `getTotal`
  methods have been renamed respectively to `getBytesFetched` and `getBytesTotal`. The `getBytesWritten` method was
  dropped from this event.
* `LocalizedStringToDateTimeTransformer` was renamed to `StringToDateTimeTransformer`.


### Removed classes, interfaces, and methods

* Removed `useInfo` from HttpTransport
* Removed deprecated `RemoveUnitSeparatorsTransformer` in favor of `RemoveControlCharactersTransformer`
* Removed `getSource`, `getSize`, and `getDestination` from TransportEvent


### API/functionality changes 

**LocalizedStringToDateTimeTransformer**:

The `LocalizedStringToDateTimeTransformer` was renamed to `StringToDateTimeTransformer` and has a different constructor signature:

```diff
-public function __construct($locale = null, $inputTimezone = null, $outputTimezone = null, $dateFormat = null, $timeFormat = null, $calendar = \IntlDateFormatter::GREGORIAN, $pattern = null)
+public function __construct($inputTimezone = null, $outputTimezone = null, $format = 'Y-m-d H:i:s', $resetFields = true)
```

This change is because the transformer no longer uses the `\DateFormatter` class, but just the `\DateTime::createFromFormat()` method, for which you can provide any format you want.


**LocalizedStringToNumberTransformer**:

The `LocalizedStringToNumberTransformer` has a different constructor signature:

```diff
-public function __construct($type = \NumberFormatter::TYPE_DOUBLE, $precision = null, $grouping = null, $roundingMode = null, $locale = null)
+public function __construct($locale = null, $precision = null, $grouping = null, $roundingMode = null)
```

This is because the type can be inferred by the `$precision` argument, and `$locale` is the main argument here (hence the transformer's name) so it makes sense to specify it first.
