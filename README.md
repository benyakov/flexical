# Flexical Online Calendar


1. Summary

    Flexical was originally derived from phpEventCalendar, and has had
    many new features added. Events have a specific date, and possibly a
    beginning and end time. They can be copied in recurring patterns and
    related to each other for batch operations. They can also be
    assigned to categories and styled in a distinguishable way.
    We use Markdown for event descriptions. Month-view calendars can be
    printed directly from web browsers with reasonable page printing
    capabilities.

    Events can be displayed from specific categories of other Flexical
    installations.  A built-in help system is included.

2. Requirements

	* PHP (5.5+)

	* MySQL (5+)

3. Installation

	1. Download the calendar archive or clone it from Github.

    2. Untar/unzip the archive into a directory on your server.
    Untarring/Unzipping will create a folder which will contain all the
    files.

    3. Point your browser to the directory where the calendar is
    installed.  You should see a form to set up the database, and then
    another one for setting up your initial user.

4. Upgrading

    1. Back up your data and your old installation.  Something could go
    wrong.  Note the database connection settings in "db.php".

    2. Unpack the distribution over your existing calendar directory.

    3. Load the calendar.  It should detect which database upgrades are
    needed and run them.  If the end result doesn't work, restore from
    your backups and ask for help.

5. License Information

    Flexical is released under the terms of the GENERAL PUBLIC
    LICENSE (GPL).  See the file help/licenses/Copying.en.txt to read
    the terms of the GENERAL PUBLIC LICENSE.  Or see
    http://www.gnu.org/copyleft/gpl.html

    phpEventCalendar by Isaac McGowan <isaac@ikemcg.com> modified by
    Jesse Jacobsen <jmatjac@gmail.com>.  The modified package is called
    FlexiCal, to avoid confusion with phpEventCalendar.

vim: tw=72
