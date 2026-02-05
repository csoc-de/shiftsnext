# Changelog

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
