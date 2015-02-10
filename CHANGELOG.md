## Version 1.0

* Now using Guzzle version 5
* Suffixed `Transport` and `Resource` with `Interface`
* Removed `useInfo` from HttpTransport
* Renamed `download` to `fetch` in `TransportInterface`. Also the `doDownload` and `needsDownload` methods were renamed
  to `doFetch` and `isFresh` respectively, with the latter having the logic inversed compared to before.
* Some `FeedEvents` constants have been renamed to reflect the naming changes. Also the constant values have changed, so
  if you're using those instead of the constants, you need to update those references.
* The `DownloadProgressEvent` was renamed to `FetchProgressEvent`, likewise the `getBytesDownloaded` and `getTotal`
  methods have been renamed respectively to `getBytesFetched` and `getBytesTotal`. The `getBytesWritten` method was
  dropped from this event.
* Removed deprecated `RemoveUnitSeparatorsTransformer` in favor of `RemoveControlCharactersTransformer`
* Removed `getSource`, `getSize`, and `getDestination` from TransportEvent
