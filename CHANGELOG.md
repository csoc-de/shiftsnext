# Changelog

## [2.12.0] - 2026-11-13

### Added

- Support Nextcloud version 36

### Changed

- Improve the [_How to assign shift admins_](https://github.com/csoc-de/shiftsnext#how-to-assign-shift-admins) section in the README
- Improve the README regarding the time zone used when rendering shifts and shift types on the shifts view
- All form inputs now use floating labels

### Fixed

- On the intial _Config missing_ view, which is shown if the app has not yet been configured, the text color now features a better contrast in regards to a light/dark background, improving readability
- A mismatch between the user's system time zone and the time zone configured in the personal settings of the logged-in user is now properly handled when creating shift types and exchanges

## [2.11.0] - 2026-06-29

### Added

- Support Nextcloud versions 34 and 35
- The calendar to which shifts are synchronized to can now be specified per shift type by appropriate group shift admins. Previously, only a single global (_common_) calendar could be defined by the Nextcloud admin.
- Synchronization to the calendar app can now be disabled per shift type

### Changed

- Update README regarding shift type calendar settings
- Remove the calendar sync button from the shifts view, as manually triggering the calendar synchronization hasn't been necessary for quite some time

### Fixed

- Deleting the _common_ or _absence_ calendar now resets the value set in the [administration settings](https://github.com/csoc-de/shiftsnext#calendar).

## [2.10.0] - 2026-02-26

### Added

- Support Nextcloud version 33

### Changed

- Improve [_Calendar sync_](https://github.com/csoc-de/shiftsnext#calendar-sync) section in the README
- Improve explanation of the _Amount_ input fields of the shift type config in the README
- Shift exchanges are now immediately marked as _done_ as soon as one of the required approvals is rejected. Previously, exchanges weren't marked as _done_ as long as at least one required approval was still pending.
- The navigation items for shift types and shift exchanges now use a more detailed label

### Fixed

- Prevent shift/type info popover glitch on shifts view
- The summary of the synchronized shift Calendar events now display the Nextcloud group's display name instead of the group's ID

## [2.9.0] - 2026-02-05

### Added

- All shifts, shift exchanges and shift admin relations of a user will now be deleted when a user is disabled. Previously, this was only the case when a user was deleted.
- The combined approval state of a shift exchange is now displayed in the upper left corner of the exchange card

### Changed

- There are now separate shift exchange edit buttons for each approval as well as a dedicated button to edit the comment only

## [2.8.0] - 2026-01-29

### Added

- Display explanation regarding calendar event fields when creating/editing shift types
- Let shift admins additionally configure the calendar event description and location when creating/editing shift types
- Include helper text explaining how to define multiple calendar event categories when creating/editing shift types

### Changed

- Switch from single line shift type description to multi line shift type description
- Adjust gaps between some form elements

### Fixed

- Fix multiple comma-separated calendar event categories being interpreted as a single category when creating/editing shift types
- Synchronize with calendar app when shifts are automatically deleted due to shift types being deleted
- Clear all red shift deletion indicators when a user navigates between weeks before the shift deletion actually concludes
