***************************************
*** FlexiCal Calendar Documentation ***
***************************************

Contents:
	I. Summary
	II. Requirements
	III. Installation
    IV. Upgrading
    V. MarkDown-style Formatting
	VI. License Information (GPL)


I. Summary

    This web calendar is originally derived from phpEventCalendar
    by Isaac McGowan.  Here's from the original summary:

        phpEventCalendar is a MySQL backed application that allows users
        to post and display events or notes on month-at-a-glance
        calendar. A user administration panel allows authorized users
        (Administrators) to control who can add, delete, and edit events
        (Editors).

        Installation instructions and documentation are available
        through the links above, and in the README file included in the
        download. If you have questions, please post them on the newly
        installed forum.

    In addition to the above, this calendar also supports color-coded
    categories for events, through-the-web backup and restoration
    of the database, and has a tabbed interface to switch between the
    month-calendar view and an event list view.  The latter view
    allows the listing of events from an arbitrary time span.  The
    default view is configurable.

    But wait, there's more!  A mini-month calendar appears in unused
    calendar boxes for the month prior and following the displayed
    month.  A category key appears displaying the names of each category
    in its own formatting for all visible categories.  In addition to
    Administrators and Editors, there is now a third authorized user who
    can view events that would otherwise be hidden.  Event descriptions
    may also be formatted with a MarkDown-style syntax.  Events can be
    copied to a single date, or forward in a repeating frequency by day,
    week, month (on date or nth day), or year.  Events can be filtered
    with a search, and filtered events can be changed or deleted in a
    batch.

    For more information, look at the files in the help directory.

II. Requirements

	* PHP 5.5

	* MySQL 5

III. Installation

	1. Download the calendar archive.

    2. Untar/unzip the archive into a directory on your server.
       Untarring/Unzipping will create a folder which will contain all
       the files.

    3. Point your browser to the directory where the calendar is
       installed.  You should see a form to set up the database, and then
       another one for setting up your initial user.

IV. Upgrading

    Upgrade is no longer fully supported from "Version 1", the original
    phpEventCalendar from Isaac McGowan.  From previous versions of
    FlexiCal:

    1. Back up your data and your old installation.  Something could go
       wrong.  Note that the file "version.php" contains your current
       software version.  Also note the database connection settings in
       "db.php".

    2. Unpack the distribution over your existing calendar directory.

    3. Load the calendar.  It should detect which database upgrades are
       needed and run them.  If the end result doesn't work, restore
       from your backups and ask for help.

V. MarkDown-style formatting in event descriptions

    Event text may be formatted using a structured formatting style
    known as MarkDown.  The calendar will interpret the structure into
    HTML code when presenting the event text in an event display window
    or when viewing events as an event list.

    The formatting engine used is PHP Markdown Extra.  This engine
    provides a few extra capabilites that may be useful in a calendar
    event description.  You can find these capabilities documented at
    http://www.michelf.com/projects/php-markdown/extra/ .
    You can find the original MarkDown formatting described at its home
    page: http://daringfireball.net/projects/markdown/ .  In particular,
    be sure to see the Syntax page.

VI. License Information

    These scripts are released under the terms of the GENERAL PUBLIC
    LICENSE (GPL).  See the file help/licenses/Copying.en.txt to read
    the terms of the GENERAL PUBLIC LICENSE.  Or see
    http://www.gnu.org/copyleft/gpl.html

    phpEventCalendar by Isaac McGowan <isaac@ikemcg.com> modified by
    Jesse Jacobsen <jmatjac@gmail.com>.  The modified package is called
    FlexiCal, to avoid confusion with phpEventCalendar.

VII. What's New

    v. 1.0
    ------

    Initial release.  It has to happen some time.

    v. 1.1 (Unreleased)
    -------------------

    - Bugfixes in the upgrade process from phpEventCalendar.
    - Fixed a few things in the handling of categories when there are
      none.
    - Added a config option controlling whether to show the category
      key.

    v. 1.2
    ------

    - Bugfix in new category selection code used when displaying
      calendars or event listings
    - Hidden category checkboxes now come up pre-checked to reflect the
      existing settings.

    v. 1.3
    ------

    - Filter events
    - Batch delete, change title, text, time begun

    v. 1.4
    ------

    - Backup and restore work as expected
    - Batch features finished

    v. 1.5
    ------

    - Fully-functional js menu in calendar view for editing
    - (Dumb) js spinners on numeric fields
    - Option to show open time in list view
    - Rounded corners with css
    - Site tabs no longer use space items

    v. 1.6
    ------

    - Will sync filtered events to Google Calendar using gcal's API,
      as a batch action in the Event List View.  This requires you to
      set up the gcal credentials in config.php, and have the Zend
      Framework for the Google API available.  It's free at
      framework.zend.com/download/gdata.  You can specify a local
      library directory in config.php.
    - New, more informative footprint layout.
    - Event titles can be styled via Modify Styles, instead of changing
      css/custom.css.

    v. 1.6.1
    --------

    - Moved stray comment from footprint so it doesn't show in page.
    - Suppresses gcal export button when not configured.

    v. 1.6.2
    --------

    - Adjusted category selector to handle names that have been
      translated to CSS classes.  (Will have to handle names that
      differ only in " "/"-" later.)

    v. 1.7
    ------

    - Added password reset mechanism for lost passwords, using an email
      message.

    v. 1.8
    ------

    - Events can be related to each other and manipulated in batches.
      This is like repeating events, only the title, description, times,
      etc. can vary between related events.

    v. 1.9
    ------

    - Added a translatable help system
    - Upgraded PHP Markdown Extra
    - Flexible templating, associating related function with the
      template
    - Customizable sitetabs
    - Extra custom templates for latex-bulletin and latex-table
    - Fixed some batch operation problems

    v. 1.10
    --------------------

    - Backup and restore scripts now check session for authorization
    - Session variables now stored in a unique part of $_SESSION
      to avoid interfering with other PHP scripts that use $_SESSION.
    - TeX-related example views tweaked for author's use
    - Improved automatic SQL "where" clause

    v. 1.11
    --------------------

    - Fixed various niggles.
    - Using mysql_real_escape_string to protect vs. sql injection attack

    v. 1.12
    --------------------

    - Bugfix: Checked uses of mysql_real_escape_string to make sure the db
      connection was opened or re-used at each use.

    v. 1.13
    --------------------

    - Streamlined and improved the internal language translation works
    - Added a template to produce an HTML table for the coming week

    v. 1.14
    --------------------

    - Added the option to copy an event a specified number of times,
      instead of fixing an end date for the repeat pattern.
    - Tweaked the copy and event forms with a little Javascript to make
      them friendlier.

    v. 1.15
    --------------------
    - Created a new Event tab from eventdisplay.  Now all events display
      onto the tab, which should remember the last event displayed.
    - Numerous style tweaks
    - Set up js to deactivate dialog controls that don't work with the
      activated ones.  Reordered some controls for ease of use.

    v. 2.0
    --------------------
    - Huge simplification to installation.  Now almost anyone can
      install it, easily.
    - Configuration settings now stored in the database.  No more
      configuration by editing a file in the filesystem.
    - Version numbers will now be Major.Minor.Tick where Minor changes
      require a database upgrade.

    v. 2.0.1
    --------------------
    Various fixups from 2.0

    v. 2.0.2
    --------------------
    Three bugfixes from v. 2.0.1

    v. 2.1.0
    --------------------
    - Editable blank cells in calendar view
    - Month is now always generated as a table; toggle removed
    - Using jQuery datepicker in event view
    - Using jQuery for event menu in calendar view

    v. 2.1.2
    --------------------
    - Bugfixes
    - Fixed up start-end time formatting in calendar view
    - Merged development branches

    v. 2.1.3
    --------------------
    - Bugfixes required after merge of development branches

    v. 2.2.0
    --------------------
    - Converted database interface library from old PHP mysql_* to PDO,
      which is more efficient (supports transactions) and is the wave of
      the future for PHP.  There are no changes to the database itself,
      but an upgrade script is necessary to change the way the calendar
      connects to it.
    - Updated event display view, making it more intuitive
    - Check-all boxes replaced with two links
    - Month and day spinners limited to sensible numbers
    - Tweaked formatting of controls in Event List View
    - Will take a short development break now after any bugfixes are
      done.

    v. 2.2.1, 2.2.2, 2.2.3
    ----------------------
    - Fixed bugs that showed up when upgrading/installing demo sites.

    v. 2.2.4
    ----------------------
    - Added missing filter match to most batch operations

    v. 3.1.22
    ----------------------
    - Haven't really kept up with this changelog; sorry.
    - Recreated Git repository for ease of distribution; ditching history	

vim: tw=72
