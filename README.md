# Shifts Next

**Note:** This app is work in progress.

This app allows managing shifts and sync them with a specified calendar.

## About this document

This document uses the terms _create_ and _assign_ regarding the creation of shifts and assigning them to a user. Creating and assigning are not two separate steps, so most of the time, these terms are interchangeable.

## Requirements

### Calendar app

This app requires the [calendar app](https://apps.nextcloud.com/apps/calendar) to be installed.

Nextcloud admins need to create two calendars in the calendar app:

1. A _common_ calendar, which the Shifts Next app uses to sync events to
2. An _absence_ calendar, which the Shifts Next app uses to check if a certain user is absent when assigning a shift to them
   - For the absence check to work, the _absence calendar_ event name has to be either the user ID or the user name. Imagine a user with ID "john.doe@example.com" and name "John Doe" exists, then the event name inside the _absence calendar_ needs to be "john.doe@example.com" or "John Doe".

## Configuration

**Note:** needs Nextcloud admin privileges.

Navigate to _Administration settings_, _Shifts Next_, to set the configuration.

### Calendar

1. Set the previously created calendars
2. Choose if you want to sync shifts to the _personal_ calendar of the user, the shift was assigned to
3. Decide whether you want to ignore the absence check for weekly shifs

### Shift exchanges

Specify who needs to approve shift exchanges in order for them to be considered _done_. See [Usage | Exchanges](#exchanges) for more info.

### Shift admins

There are certain functionalities of the app which are not available to all users, including

- creating/modifying shift types,
- assigning shifts to users,
- deleting shifts and
- approving shift exchanges (depending on the value of the [shift exchanges](#shift-exchanges) setting).

For these functionalities you need to define shift admins per Nextcloud group. We deliberately decided against using regular Nextcloud group admins to restrict these functionalities as it might be undesirable to give users regular Nextcloud group admin privileges merely because this app requires it.

## Usage

### Types

Before being able to assign any shifts, you need to create shift types.

**Note:** Most fields cannot be edited after a shift type has been created, with the exception being _Name_, _Color_, _Active_, _Description_ and the _Calendar event fields_.

#### Group

Shift types are bound to a Nextcloud group. A user needs to be a member of a shift type's group in order to get assigned a shift of that type.

#### Name

Choose a name to identify the shifts created from this type.

#### Color

Select a color to easily distinguish between multiple types.

#### Active

Shift types need to be marked as active in order to create shifts from them.

#### Description

Add a description to include more information what this type is about.

#### Calendar event fields

The values of these fields will be inserted into the corresponding calendar event fields when synchronizing shifts to the calendar app.

- _Description_
- _Location_
- _Categories_

#### Repetition

##### Frequency & Interval

These two settings in conjuction with the _Reference_ and _Amount_ fields control when shifts are considered _creatable_.

For example, setting _Frequency_ to _weekly_ (which currently is the only option) and _Interval_ to _2_ reads as: allow shift admins to create shifts from this type _every 2 weeks_, relative to the reference field. See [config](#config-weekly-by-day) for more info.

##### Weekly type

You need to define whether shifts occur on specific days of the week or span a whole [week](#week-definition).

- For day of week specific shifts, set _Weekly type_ to _By day_
- For whole week shifts, set _Weekly type_ to _By week_

##### Config (Weekly by day)

_Reference date & time_, _Time zone_ and _[Interval](#frequency--interval)_ determine if shifts can be created in any given week. Furthermore, they also determine the earliest possible moment in time a shift can be created. On days a shift is determined to be _creatable_, the time part of _Reference date & time_ is used as the start time of a shift. The end time is calculated by adding the specified _Duration_ to the start time.

The _Amount_ input fields set the number of occurrences a shift can be created on a specific day of week. This will be _1_ most of the time.

##### Config (Weekly by week)

_Reference week_ and _[Interval](#frequency--interval)_ determine if shifts can be created for a given week. Furthermore, they also determine the earliest ISO week a shift can be created.

_Amount_ sets the number of occurrences a shift can be created in a single week. This will be _1_ most of the time.

### Shifts

The second row of the table, labeled _Open shifts_, displays all shifts determined to be _creatable_ for the selected week. To be more precise, it actually displays shift types, because not yet assigned shifts don't actually exist: assigning a shift means creating a shift. _By week_ shifts/types are displayed in the second column. The remaining columns to the right display _By day_ shifts/types. The exact day column, where a shift/type is displayed in, depends on the shift's start time/the type's reference time + time zone, respectively. **Note:** The shift/types displayed in the _By day_ columns are browser time zone aware. If, for example, a shift type named _Foo_ exists, with its reference set to `2025-07-14T06:00:00+02:00[Europe/Berlin]` and Monday amount set to _0_ and Tuesday amount set to _1_, this type will displayed in the Monday column with amount _1_, if a user's browser time zone is _America/Los_Angeles_, because `2025-07-14T06:00:00+02:00[Europe/Berlin]` translates to `2025-07-13T21:00:00-07:00[America/Los_Angeles]`, i.e. the reference time is on a different day.

Shifts can be assigned by clicking the 4-way arrow button of the colored boxes in the _Open shifts_ row and then clicking into the cell of a user's row. Already assigned shifts can also be deleted or moved to another user. Assigning, moving and deleting require shift admin privileges.

### Exchanges

Exchanges can be used to request swapping an already assigned shift with a shift of another user. Alternatively, they can be used to request transferring a shift to another user.

#### Create/request a shift exchange

The form is split into _A_ and _B_.

##### A

Select a user (which should be yourself most of the time, except when creating an exchange for others as a shift admin), date and shift.

##### B

For regular exchanges, repeat the steps from _[A](#a)_. If creating a transfer, you only need to choose a user you want to transfer _shift A_ to.

#### Approve pending shift exchanges

After a shift exchange has been created, it needs to be approved by either

- the participating users,
- a shift admin,
- or both (depending on the value of the [shift exchanges](#shift-exchanges) setting).

When all required parties have either approved or rejected the exchange, the exchange will change its state to _done_.

### Calendar sync

Whenever a shift is assigned, moved, deleted, transferred or exchanged, it will get synced to the _common_ calendar automatically.

## Additional information

### Week definition

This app always uses the [ISO 8601 week numbering system](https://en.wikipedia.org/wiki/ISO_week_date), which starts on Monday and ends on Sunday, no matter the week numbering system of a user's own locale.

### Software used

This app relies heavily on the new [JavaScript Temporal API](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Temporal). As of January 14, 2026, only Firefox and Chrome ship this API in their stable browser versions, so this app uses [fullcalendar/temporal-polyfill](https://github.com/fullcalendar/temporal-polyfill) created by [Adam Shaw](https://github.com/arshaw).
