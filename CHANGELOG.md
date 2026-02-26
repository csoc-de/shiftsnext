# Changelog

## [2.10.0] - 2026-02-26

### Added

- Support Nextcloud version 33

### Changed

- Improve _Calendar sync_ section in the README
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
